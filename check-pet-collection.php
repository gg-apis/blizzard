<?php

$petCollection = json_decode(
    file_get_contents(__DIR__ . '/tests/Fixture/mock_blizzard/fetch_pet_collection.json'),
    true,
    flags: JSON_THROW_ON_ERROR
);

foreach ($petCollection['pets'] as $pet) {
    var_dump($pet['stats']);
}
