<?php

// Read only
return [
    "all" => [
        "/",
        "/about",
        "/contact",
        "/tos",
        "/posts/{page:i}",
        "/post/{post:a}",
        "/add_comment/{post:a}",
        "/report/{post:a}",
        "/login",
    ],
    "lector" => [
        "/favorite/{from:*}"
    ],
    "admin" => [
        //
    ]
];