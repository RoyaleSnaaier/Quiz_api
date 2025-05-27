<?php

require_once '../include/class/response.php';
require_once '../include/db.php';
require_once '../include/class/post.php';

if (isset($_GET['postId'])) {

    //welke entry point is dit?

    //update
    if ($_SERVER['REQUEST_METHOD'] === 'PUT') {   

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }
        try {
            $post = new Post (
                id: $jsonData-> id ?? -1,
                author: $jsonData-> author ?? '',
                message: $jsonData-> message ?? ''
            );

            $sql = "UPDATE post SET author = :author, message = :message WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':author', $post->getAuthor());
            $stmt->bindParam(':message', $post->getMessage());
            $stmt->bindParam(':id', $post->getId(), PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Post updated successfully', $post);
            } else {
                new Response('No post found with the given ID', null, 404);
            }

        } catch(PostException $exception) {
            new Response('Error creating post object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
        try {
            $postId = $_GET['postId'];
            $sql = "DELETE FROM post WHERE id = :id";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Post deleted successfully', null);
            } else {
                new Response('No post found with the given ID', null, 404);
            }
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        //get post by id
        $postId = $_GET['postId'];
        $sql = "SELECT * FROM post WHERE id = :id";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':id', $postId, PDO::PARAM_INT);
        $stmt->execute();
        $post = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($post) {
            new Response('Success', $post);
        } else {
            new Response('Post not found', null, 404);
        }

    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        //create post
        $post = new Post(
            id: -1, // New post, so no ID yet
            author: $jsonData->author ?? '',
            message: $jsonData->message ?? '',
        );

        $sql = "INSERT INTO post (author, message) VALUES (:author, :message)";
        $stmt = getPDO()->prepare($sql);
        $stmt->bindParam(':author', $post->getAuthor());
        $stmt->bindParam(':message', $post->getMessage());
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            new Response('Post created successfully', $post);
        } else {
            new Response('Failed to create post', null, 500);
        }
    } else {
        new Response('Method not allowed', null, 405);
    }

} else {

    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
try {
    $sql = "SELECT * FROM post";
    $stmt = getPDO()->prepare($sql);
    $stmt->execute();
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($posts) {
        new Response('Success', $posts);
    } else {
        new Response('No posts found', null, 404);
    }

} catch (PDOException $e) {
    new Response('Error', $e->getMessage(), 500);
}
    } else if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        $rawData = file_get_contents("php://input");

        if (!$jsonData = json_decode($rawData)) {
            new Response('Invalid JSON data', null, 400);
        }

        try {
            $post = new Post(
                id: -1,
                author: $jsonData->author ?? '',
                message: $jsonData->message ?? ''
            );

            $author = $post->getAuthor();
            $message = $post->getMessage();

            $sql = "INSERT INTO post (author, message) VALUES (:author, :message)";
            $stmt = getPDO()->prepare($sql);
            $stmt->bindParam(':author', $author);
            $stmt->bindParam(':message', $message);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                new Response('Post created successfully', $post);
            } else {
                new Response('Failed to create post', null, 500);
            }

        } catch (PostException $exception) {
            new Response('Error creating post object', $exception->getMessage(), 400);
        } catch (PDOException $exception) {
            new Response('Error', $exception->getMessage(), 500);
        }

    } else {
        new Response('Method not allowed', null, 405);
    }
    
}


