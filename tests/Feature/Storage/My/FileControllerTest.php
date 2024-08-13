<?php

namespace Tests\Feature\Storage\My;

use App\Enums\Application\ApplicationType;
use App\Enums\Storage\My\FolderType;
use App\Models\Assess\AssessmentActivity;
use App\Models\Assess\AssessmentItemResponse;
use App\Models\Auth\Role;
use App\Models\Auth\User;
use App\Models\Comments\Comment;
use App\Models\Customer\Libryo;
use App\Models\Customer\Organisation;
use App\Models\Ontology\UserTag;
use App\Models\Storage\My\File;
use App\Models\Storage\My\Folder;
use App\Models\Tasks\Task;
use App\Models\Tasks\TaskActivity;
use App\Services\Customer\ActiveLibryosManager;
use App\Services\Customer\ActiveOrganisationManager;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Feature\My\MyTestCase;

class FileControllerTest extends MyTestCase
{
    public function testShow(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org);
        $this->signIn($user);
        $this->activateAllStreams($user);
        $folder = Folder::factory()->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $file = File::factory()->for($folder)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $route = route('my.drives.files.show', ['file' => $file->id]);
        $response = $this->get($route);
        $response->assertSee($file->title);

        $this->flushSession();
        $this->signIn($user);

        // when file is org file, but you activate single mode, it should redirect to root
        $libryo = Libryo::factory()->for($org)->create();
        $user->libryos()->attach($libryo);
        $response = $this->followingRedirects()
            ->post(route('my.libryos.activate', ['libryo' => $libryo->id]))
            ->assertSuccessful();
        $response->assertDontSee($file->title);
    }

    public function testDestroy(): void
    {
        $user = User::factory()->create();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $this->signIn($user);

        app(ActiveOrganisationManager::class)->activate($org->id);

        $this->activateAllStreams($user);

        $role = Role::create(['title' => 'My Users', 'app' => ApplicationType::my(), 'permissions' => []]);
        $user->roles()->sync([$role->id]);

        $user->flushComputedPermissions();

        $folder = Folder::factory()->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $file = File::factory()->for($folder)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);

        $route = route('my.drives.files.destroy', ['file' => $file->id]);
        $response = $this->followingRedirects()->delete($route)->assertSuccessful();

        $response->assertSee($folder->title);

        $this->assertNotNull($file->refresh()->deleted_at);

        $file->refresh()->restore();

        $this->assertNull($file->deleted_at);

        $response = $this->followingRedirects()
            ->delete(route('my.drives.files.bulk.destroy'), ["actions-checkbox-{$file->id}" => true])
            ->assertSuccessful();

        $response->assertSee($folder->title);

        $this->assertNotNull($file->refresh()->deleted_at);

        // shouldn't be able to delete files of type org that aren't part of an org
        $file = File::factory()->for($folder)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $this->assertFalse($user->can('delete', $file));

        $libryoFolder = Folder::factory()->create([
            'folder_type' => FolderType::libryo()->value,
        ]);
        // shouldn't be able to delete files of type libryo that aren't part of a libryo
        $file = File::factory()->for($libryoFolder)->create([
            'folder_type' => FolderType::libryo()->value,
        ]);
        $this->assertFalse($user->can('delete', $file));

        // should be able to delete files of type libryo that are part of a libryo that you're part of and
        // you either created the file or you're an org admin
        $libryo = Libryo::factory()->create();
        $file = File::factory()->for($libryoFolder)->create([
            'folder_type' => FolderType::libryo()->value,
            'place_id' => $libryo->id,
        ]);
        $user->libryos()->attach($libryo);
        $user->organisations()->attach($libryo->organisation, ['is_admin' => true]);
        $this->assertTrue($user->can('delete', $file));

        // should be able to delete files of type org that are part of the org
        $file = File::factory()->for($folder)->create([
            'folder_type' => FolderType::organisation()->value,
            'organisation_id' => $org->id,
        ]);
        $this->assertTrue($user->can('delete', $file));

        // test that it responds with a turbo response when it's from a relation
        // instead of a redirect
        $task = Task::factory()->create();
        $route = route('my.drives.files.destroy', ['file' => $file->id]);
        $response = $this->delete($route, ['relation' => 'task', 'related_id' => $task->id])->assertSuccessful();
        $response->assertSee('turbo-stream');
        $response->assertSee('files-for-task-' . $task->id);
    }

    public function testUpload(): void
    {
        [$createdFile, $user, $org] = $this->uploadFileTest();

        $this->assertTrue($createdFile->title === 'document');
        $this->assertTrue($createdFile->extension === 'pdf');
        $this->assertTrue($createdFile->size === 1024);

        $libryo = Libryo::factory()->for($org)->create();
        $folder = Folder::factory()->for($org)->create();
        $libryo->users()->attach($user);
        app(ActiveLibryosManager::class)->activate($user, $libryo);

        // test uploading images
        $uploadedFile = UploadedFile::fake()->image(
            'image.jpg',
            200,
            100
        );
        $route = route('my.drives.files.upload');
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id]);
        $response->assertSuccessful();
        $folder->update(['folder_type' => FolderType::organisation()->value]);
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id]);
        $response->assertSuccessful();
        $createdFile = File::all()->last();
        $this->assertTrue($createdFile->title === 'image');
        $this->assertTrue($createdFile->extension === 'jpg');

        Storage::assertExists($createdFile->path);
        Storage::assertExists(str_replace('/original/', '/small/', $createdFile->path));
        Storage::assertExists(str_replace('/original/', '/medium/', $createdFile->path));

        // test uploading for related files
        $aiResponse = AssessmentItemResponse::factory()->create();
        $count = AssessmentActivity::count();
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id, 'relation' => 'assessment-item-response', 'related_id' => $aiResponse->id]);
        $response->assertSuccessful();
        $this->assertGreaterThan($count, AssessmentActivity::count());

        $task = Task::factory()->create();
        $count = TaskActivity::count();
        $newTagName = Str::random();
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id, 'relation' => 'task', 'related_id' => $task->id, 'user_tags' => [$newTagName]]);
        $response->assertSuccessful();
        $this->assertGreaterThan($count, TaskActivity::count());
        $this->assertTrue(UserTag::forOrganisation($org->id)->where('title', $newTagName)->exists());

        // test with existing UserTags
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id, 'relation' => 'task', 'related_id' => $task->id, 'user_tags' => [UserTag::first()->id]]);
        $response->assertSuccessful();

        $comment = Comment::factory()->create([
            'organisation_id' => $org->id,
            'commentable_type' => 'task',
            'commentable_id' => $task->id,
        ]);
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id, 'relation' => 'comment', 'related_id' => $comment->id]);
        $response->assertSuccessful();

        // can't upload files of non-supported mime-type
        $uploadedFile = UploadedFile::fake()->create(
            'document.csh',
            1,
            'application/x-csh'
        );
        $route = route('my.drives.files.upload');
        $response = $this->withExceptionHandling()->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id]);
        $response->assertStatus(415);

        // can't upload files when org has run out of storage
        $org->updateSetting('storage_allocation', 0);
        app(ActiveLibryosManager::class)->flushCache();
        $uploadedFile = UploadedFile::fake()->image(
            'image.jpg',
            200,
            100
        );

        $route = route('my.drives.files.upload');
        $response = $this->withExceptionHandling()->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id]);
        $response->assertStatus(422);
    }

    private function uploadFileTest(): array
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();
        $this->activateAllStreams($user);

        Storage::fake('local');
        $folder = Folder::factory()->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);

        // test uploading normal doc
        $uploadedFile = UploadedFile::fake()->create(
            'document.pdf',
            1,
            'application/pdf'
        );

        $route = route('my.drives.files.upload');
        $response = $this->post($route, ['file' => $uploadedFile, 'folder_id' => $folder->id]);
        $response->assertSuccessful();
        $createdFile = File::all()->last();

        $this->assertSame($user->id, $createdFile->author_id);

        return [$createdFile, $user, $org];
    }

    public function testDownloadAndStream(): void
    {
        [$createdFile, $user, $org] = $this->uploadFileTest();

        $route = route('my.drives.files.download', ['file' => $createdFile->id]);
        $response = $this->get($route);
        $response->assertSuccessful();
        $response->assertHeader('Content-Type', 'application/pdf');

        $route = route('my.drives.files.stream', ['file' => $createdFile->id]);
        $response = $this->get($route);
        $response->assertSuccessful();
        $response->assertHeader('Content-Disposition', 'inline; filename=document');
    }

    public function testEdit(): void
    {
        $user = $this->signIn($this->mySuperUser());
        $file = File::factory()->create();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $file->update(['organisation_id' => $org->id, 'folder_type' => FolderType::organisation()->value]);

        $route = route('my.drives.files.edit', ['file' => $file->id]);
        $response = $this->get($route)->assertSuccessful();
        $response->assertSeeSelector('//header//*[text()[contains(.,"' . $file->title . '")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Name")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Expiry Date")]]');
        $response->assertSeeSelector('//label[text()[contains(.,"Tags")]]');
        $response->assertSeeSelector('//input[@value="' . $file->title . '"]');
    }

    public function testUpdate(): void
    {
        $user = User::factory()->create();
        $role = Role::create(['title' => 'My Users', 'app' => ApplicationType::my(), 'permissions' => []]);
        $user->roles()->sync([$role->id]);
        $user->flushComputedPermissions();

        $user = $this->signIn($user);
        $file = File::factory()->create();

        $route = route('my.drives.files.update', ['file' => $file->id]);
        $response = $this->withExceptionHandling()->put($route)->assertForbidden();

        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $file->update(['organisation_id' => $org->id, 'folder_type' => FolderType::organisation()->value]);

        $this->activateAllStreams($user);

        $newTitle = 'New Title';
        $count = UserTag::count();
        $response = $this->followingRedirects()->put($route, ['user_tags' => ['New Tag'], 'title' => $newTitle])->assertSuccessful();
        $this->assertSame($newTitle, $file->refresh()->title);
        $this->assertGreaterThan($count, UserTag::count());
    }

    public function testReplace(): void
    {
        [$createdFile, $user, $org] = $this->uploadFileTest();

        $uploadedFile = UploadedFile::fake()->create(
            'document2.pdf',
            1,
            'application/pdf'
        );
        $originalPath = $createdFile->path;

        Storage::assertExists($originalPath);

        $route = route('my.drives.files.replace', ['file' => $createdFile->id]);
        $response = $this->followingRedirects()->put($route, ['file' => $uploadedFile])->assertSuccessful();

        Storage::assertMissing($originalPath);

        $createdFile = $createdFile->fresh();

        Storage::assertExists($createdFile->path);
    }

    public function testMove(): void
    {
        $user = $this->mySuperUser();
        $org = Organisation::factory()->create();
        $user->organisations()->attach($org, ['is_admin' => true]);
        $this->signIn($user);

        $folders = Folder::factory(2)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $file = File::factory()->for($org)->for($folders[0])->create([
            'folder_type' => FolderType::organisation()->value,
        ]);

        $this->assertFalse($folders[1]->files->contains($file));

        $route = route('my.drives.files.move', ['file' => $file->id]);
        $response = $this->followingRedirects()->put($route, ['destination' => $folders[1]->id])->assertSuccessful();

        $folder = $folders[1]->fresh();

        $this->assertTrue($folder->files->contains($file));
    }

    public function testSearchForOrg(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $folder = Folder::factory()->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $files = File::factory(2)->for($folder)->for($org)->create([
            'folder_type' => FolderType::organisation()->value,
        ]);
        $this->testSearch($files);

        $this->activateAllStreams($user);
        $this->testSearch($files);
    }

    public function testSearchForLibryo(): void
    {
        [$user, $libryo, $org] = $this->initUserLibryoOrg();

        $folder = Folder::factory()->create([
            'folder_type' => FolderType::libryo()->value,
        ]);
        $files = File::factory(2)->for($folder)->create([
            'folder_type' => FolderType::libryo()->value,
            'place_id' => $libryo->id,
        ]);

        $this->testSearch($files);
    }

    private function testSearch(Collection $files): void
    {
        $file = $files[0];
        $file2 = $files[1];
        $route = route('my.drives.files.index');
        $response = $this->get($route);
        $response->assertSee($file->title);

        $userTag = UserTag::factory()->create();
        $file->userTags()->attach($userTag);

        $route = route('my.drives.files.index', ['search' => $file->title]);
        $response = $this->get($route);
        $response->assertSee($file->title);
        $response->assertDontSee($file2->title);
    }
}
