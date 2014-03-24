<?php

/**
 * This scripts generates random posts
 */

require 'cli-bootstrap.php';

$faker = Faker\Factory::create();
$log   = new Phalcon\Logger\Adapter\Stream('php://stdout');

$log->info('Start');

/** @var Phalcon\Db\AdapterInterface $database */
$database = $di->getShared('db');

$database->begin();

for ($i = 0; $i <= 20; $i++) {

    $title = $faker->company;

    $category               = new Phosphorum\Models\Categories();
    $category->name         = $title;
    $category->slug         = Phalcon\Tag::friendlyTitle($title);
    $category->number_posts = 0;

    if (!$category->save()) {

        var_dump($category->getMessages());
        $database->rollback();
        break;
    }

    $log->info('Category: '.$category->name);
}

for ($i = 0; $i <= 50; $i++) {

    $user           = new Phosphorum\Models\Users();
    $user->name     = $faker->name;
    $user->login    = $faker->userName;
    $user->email    = $faker->email;
    $user->timezone = $faker->timezone;

    if (!$user->save()) {

        var_dump($user->getMessages());
        $database->rollback();
        break;
    }

    $log->info('User: '.$user->name);
}
$database->commit();

$categoryIds = Phosphorum\Models\Categories::find(['columns' => 'id'])->toArray();
$userIds    = Phosphorum\Models\Users::find(['columns' => 'id'])->toArray();

$database->begin();
for ($i = 0; $i <= 500; $i++) {

    $title   = $faker->company;
    $content = $faker->text();

    $post          = new Phosphorum\Models\Posts();
    $post->title   = $title;
    $post->slug    = Phalcon\Tag::friendlyTitle($title);
    $post->content = $content;


    $userRandId     = array_rand($userIds);
    $post->users_id = $userIds[$userRandId]['id'];

    $categoryRandId      = array_rand($categoryIds);
    $post->categories_id = $categoryIds[$categoryRandId]['id'];

    if (!$post->save()) {

        var_dump($post->getMessages());
        $database->rollback();
        break;
    }

    $log->info('Post: '.$post->title);
}
$database->commit();

$postIds    = Phosphorum\Models\Posts::find(['columns' => 'id'])->toArray();

$database->begin();
for ($i = 0; $i <= 1000; $i++) {

    $reply = new \Phosphorum\Models\PostsReplies();

    $reply->content = $faker->paragraph();

    $postRandId      = array_rand($postIds);
    $reply->posts_id= $postIds[$postRandId]['id'];

    $userRandId     = array_rand($userIds);
    $reply->users_id = $userIds[$userRandId]['id'];

    if (!$reply->save()) {

        var_dump($reply->getMessages());
        $database->rollback();
        break;
    }

    $log->info('Reply to post: '.$reply->posts_id);
}

$database->commit();
