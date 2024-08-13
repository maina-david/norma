<?php

namespace Tests\Feature\Collaborators;

use App\Http\Controllers\Collaborators\Collaborate\Applications\StageOneController;
use App\Models\Geonames\CountryInfo;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class StageOneApplicationTest extends TestCase
{
    /**
     * @return void
     */
    public function testRedirectionToNewEndpoint(): void
    {
        $this->get(route('collaborate.collaborator-application.stage-one.old'))
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', 1));
    }

    /**
     * @return void
     */
    public function testRedirectionToCorrectStage(): void
    {
        $this->assertGuest();

        $this->get(route('collaborate.collaborator-application.stage-one.create', 2))
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', 1));

        $this->get(route('collaborate.collaborator-application.stage-one.create', 3))
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', 1));

        $this->get(route('collaborate.collaborator-application.stage-one.create', 4))
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', 1));

        $this->get(route('collaborate.collaborator-application.stage-one.create', 10))
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', 1));

        $this->post(route('collaborate.collaborator-application.stage-one.store', 3))
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', 1));
    }

    /**
     * @return void
     */
    public function testRenderingOfCorrectPartial(): void
    {
        $key = str_replace('\\', '', StageOneController::class);

        $this->session([
            "{$key}1" => [],
            "{$key}2" => [],
            "{$key}3" => [],
            "{$key}4" => [],
        ]);

        $this->followingRedirects()
            ->get(route('collaborate.collaborator-application.stage-one.create', 1))
            ->assertSee('Personal Information')
            ->assertSee('First Name');

        $this->followingRedirects()
            ->get(route('collaborate.collaborator-application.stage-one.create', 2))
            ->assertSee('Education & Skills')
            ->assertSee('University')
            ->assertSee('Year of Study/Qualification');

        $this->followingRedirects()
            ->get(route('collaborate.collaborator-application.stage-one.create', 3))
            ->assertSee('Quiz')
            ->assertSee('Guidelines')
            ->assertSee('Legal Text');

        $this->followingRedirects()
            ->get(route('collaborate.collaborator-application.stage-one.create', 4))
            ->assertSee('Terms & Conditions')
            ->assertSee('Libryo is committed to protecting and respecting your privacy.');
    }

    /**
     * Test the given stage.
     *
     * @param int    $current
     * @param array  $data
     * @param string $remove
     * @param array  $storeSee
     * @param bool   $noSession
     */
    protected function validateStage(int $current, array $data, string $remove, array $storeSee, bool $last = false)
    {
        $key = str_replace('\\', '', StageOneController::class);
        $value = $data[$remove];
        unset($data[$remove]);

        $this->from(route('collaborate.collaborator-application.stage-one.create', $current))
            ->post(route('collaborate.collaborator-application.stage-one.store', $current), $data)
            ->assertRedirect(route('collaborate.collaborator-application.stage-one.create', $current))
            ->assertSessionHasErrors([$remove]);

        $data[$remove] = $value;

        $response = $this->followingRedirects()
            ->post(route('collaborate.collaborator-application.stage-one.store', $current), $data);

        foreach ($storeSee as $text) {
            $response->assertSee($text);
        }

        $files = array_keys(array_filter($data, fn ($item) => $item instanceof UploadedFile));

        $session = session($key . $current);
        $sent = $data;

        foreach ($files as $file) {
            unset($session[$file], $sent[$file]);
        }

        if (!$last) {
            sort($session);
            sort($sent);

            $this->assertSame($session, $sent);

            $response = $this->get(route('collaborate.collaborator-application.stage-one.create', $current));

            foreach ($data as $value) {
                if (is_string($value) || is_numeric($value)) {
                    $response->assertSee($value);
                }
            }
        }
    }

    /**
     * @return void
     */
    public function testStorageOfApplicationInTheSession(): void
    {
        CountryInfo::create(['iso_alpha2' => 'KE', 'name' => 'Kenya']);

        $firstName = $this->faker->firstName();

        $data = [
            'first_name' => $firstName,
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->safeEmail(),
            'nationality' => 'Kenya',
            'current_residency' => 'Kenya',
        ];

        $this->validateStage(1, $data, 'first_name', ['Education & Skills', 'University', 'Year of Study/Qualification']);

        $data = [
            'skills' => ['Legal writing'],
            'university' => 'Other',
            'year_of_study' => '1st Year',
            'language' => ['eng'],
            'proficiency' => ['1'],
            'education_transcript' => UploadedFile::fake()->image('transcript.jpg'),
            'curriculum_vitae' => UploadedFile::fake()->image('cv.jpg'),
        ];

        $this->validateStage(2, $data, 'curriculum_vitae', ['Guidelines']);

        $data = ['quiz' => $this->faker->paragraphs(2, true)];

        $this->validateStage(3, $data, 'quiz', ['Quiz']);

        $data = [
            'process_personal_data' => 'on',
            'allows_communication' => 'on',
        ];

        $this->validateStage(4, $data, 'process_personal_data', ["Thank you {$firstName}"], true);
    }
}
