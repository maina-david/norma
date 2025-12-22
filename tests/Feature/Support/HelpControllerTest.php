<?php

namespace Tests\Feature\Support;

use Tests\Feature\My\MyTestCase;

class HelpControllerTest extends MyTestCase
{
    public function testSearchPortal(): void
    {
        [$user, $norma, $org] = $this->initUserNormaOrg();
        $response = $this->get(route('my.help.search.success.portal'))->assertRedirect();

        $response->assertRedirectContains('https://success.norma.com');
    }
}
