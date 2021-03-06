<?php

/*
 * This file is part of Flarum.
 *
 * (c) Toby Zerner <toby.zerner@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Flarum\Tests\Api\Controller;

use Flarum\Api\Controller\CreateGroupController;
use Flarum\Group\Group;
use Illuminate\Support\Str;

class CreateGroupControllerTest extends ApiControllerTestCase
{
    protected $controller = CreateGroupController::class;

    protected $data = [
        'nameSingular' => 'flarumite',
        'namePlural' => 'flarumites',
        'icon' => 'test',
        'color' => null
    ];

    /**
     * @test
     * @expectedException \Illuminate\Validation\ValidationException
     * @expectedExceptionMessage The given data was invalid.
     */
    public function admin_cannot_create_group_without_data()
    {
        $this->actor = $this->getAdminUser();

        $this->callWith();
    }

    /**
     * @test
     */
    public function admin_can_create_group()
    {
        $this->actor = $this->getAdminUser();

        $response = $this->callWith($this->data);

        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getBody()->getContents(), true);
        $group = Group::where('icon', $this->data['icon'])->firstOrFail();

        foreach ($this->data as $property => $value) {
            $this->assertEquals($value, array_get($data, "data.attributes.$property"), "$property not matching to json response");
            $property = Str::snake($property);
            $this->assertEquals($value, $group->{$property}, "$property not matching to database result");
        }
    }

    /**
     * @test
     * @expectedException \Flarum\User\Exception\PermissionDeniedException
     */
    public function unauthorized_user_cannot_create_group()
    {
        $this->actor = $this->getNormalUser();

        $this->callWith($this->data);
    }

    public function tearDown()
    {
        Group::where('icon', $this->data['icon'])->delete();
        parent::tearDown();
    }
}
