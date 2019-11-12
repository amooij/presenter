<?php

namespace CultureGr\Presenter\Tests;

use CultureGr\Presenter\Presenter;
use CultureGr\Presenter\Tests\Fixtures\User;
use CultureGr\Presenter\Tests\Fixtures\Paginator;
use CultureGr\Presenter\Tests\Fixtures\UserPresenter;

class PresenterTest extends TestCase
{
    /** @test */
    public function it_can_be_instantiated()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
        };

        $this->assertInstanceOf(Presenter::class, $presenter);

    }

    /** @test */
    public function it_proxies_the_original_model_attributes()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
        };

        $this->assertSame($user->firstname, $presenter->firstname);
        $this->assertSame($user->lastname, $presenter->lastname);
        $this->assertSame($user->email, $presenter->email);
    }

    /** @test */
    public function it_proxies_the_original_model_methods()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
        };

        $this->assertSame($user->fullname(), $presenter->fullname());
    }

    /** @test */
    public function it_wraps_the_original_model()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
        };

        $this->assertSame($user, $presenter->getModel());
    }

    /*
    |--------------------------------------------------------------------------
    | Test presenting single model
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_can_be_present_a_single_model()
    {
        $user = factory(User::class)->make();

        $presentedUser = UserPresenter::make($user);

        $this->assertInstanceOf(UserPresenter::class, $presentedUser);
    }

    /** @test */
    public function a_presented_model_can_be_serialized()
    {
        $user = factory(User::class)->make();

        $presentedUser = UserPresenter::make($user);

        $this->assertSame($user->toArray(), $presentedUser->toArray());
    }

    /** @test */
    public function a_presented_model_can_define_the_array_of_attributes_that_can_be_serialized()
    {
        $user = factory(User::class)->make();

        $presenter = new class($user) extends Presenter
        {
            public function toArray()
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email
                ];
            }
        };

        $this->assertSame([
            'fullname' => $user->fullname(),
            'email' => $user->email
        ], $presenter->toArray());
    }

    /*
    |--------------------------------------------------------------------------
    | Test presenting collections
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_can_be_present_a_collection_of_models()
    {
        $users = factory(User::class, 3)->make();

        $presentedUsers = UserPresenter::collection($users);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $presentedUsers);
        $this->assertInstanceOf(UserPresenter::class, $presentedUsers->first());
    }

    /** @test */
    public function a_presented_collection_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();

        $presentedUsers = UserPresenter::collection($users);

        $this->assertSame($users->toArray(), $presentedUsers->toArray());
    }

    /** @test */
    public function a_presented_collection_can_define_the_array_of_attributes_that_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();

        // TODO: Make a UserCollectionPresenter fixture
        $presentedUsers = (new class($users->first()) extends Presenter
        {
            public function toArray()
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email
                ];
            }
        })::collection($users);

        $this->assertSame([
            'fullname' => $users[0]->fullname(),
            'email' => $users[0]->email
        ], $presentedUsers->toArray()[0]);
    }

    /*
    |--------------------------------------------------------------------------
    | Test presenting paginated models
    |--------------------------------------------------------------------------
    |
    */

    /** @test */
    public function it_can_be_present_a_paginated_collection_of_models()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = UserPresenter::pagination($paginator);

        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $presentedUsers);
        $this->assertInstanceOf(UserPresenter::class, $presentedUsers['data']->first());
    }

    /** @test */
    public function a_presented_paginated_collection_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = UserPresenter::pagination($paginator);

        $this->assertSame($users->toArray(), $presentedUsers->toArray()['data']);
    }

    /** @test */
    public function a_presented_paginated_collection_can_define_the_array_of_attributes_that_can_be_serialized()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        // TODO: Make a UserCollectionPresenter fixture
        $presentedUsers = (new class($users->first()) extends Presenter
        {
            public function toArray()
            {
                return [
                    'fullname' => $this->fullname(),
                    'email' => $this->email
                ];
            }
        })::pagination($paginator);

        $this->assertSame([
            'fullname' => $users[0]->fullname(),
            'email' => $users[0]->email
        ], $presentedUsers->toArray()['data'][0]);
    }

    /** @test */
    public function a_presented_paginated_collection_can_define_the_links_keys()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = (new class($users->first()) extends Presenter
        {
        })::pagination($paginator);

        $this->assertSame([
            "first" => "http://example.com/pagination?page=1",
            "last" => "http://example.com/pagination?page=100",
            "prev" => null,
            "next" => 2
        ], $presentedUsers->toArray()['links']);
    }

    /** @test */
    public function a_presented_paginated_collection_can_define_the_meta_keys()
    {
        $users = factory(User::class, 3)->make();
        $paginator = (new Paginator)($users);

        $presentedUsers = (new class($users->first()) extends Presenter
        {
        })::pagination($paginator);

        $this->assertSame([
            'current_page' => 1,
            'from' => 1,
            'last_page' => 100,
            'path' => 'http://example.com/pagination',
            'per_page' => 15,
            'to' => 10,
            'total' => 10
        ], $presentedUsers->toArray()['meta']);
    }
}
