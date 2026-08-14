<?php

namespace Database\Seeders;

use App\Models\Animal;
use App\Models\AnimalPhoto;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AnimalSeeder extends Seeder
{
    public function run(): void
    {
        $animals = [

            // 1 - 10
            [
                'id' => 1, 'name' => 'Kiwi', 'type' => 'bird',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 0.12,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly green parrot with an active and curious personality.',
                'story' => 'Kiwi was rescued after being found alone and is now ready for a safe permanent home.',
            ],
            [
                'id' => 2, 'name' => 'Mily', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.20,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm black-and-white cat that enjoys quiet environments.',
                'story' => 'Mily was rescued from a residential area and successfully completed his health check.',
            ],
            [
                'id' => 3, 'name' => 'Buddy', 'type' => 'dog',
                'gender' => 'male', 'age' => 4, 'size' => 'large', 'weight' => 22.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A loyal and energetic dog who enjoys outdoor activities.',
                'story' => 'Buddy was rescued from the street and is now looking for an active family.',
            ],
            [
                'id' => 4, 'name' => 'Snow', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.40,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A gentle white rabbit with a calm temperament.',
                'story' => 'Snow was surrendered to the shelter and is ready for a caring home.',
            ],
            [
                'id' => 5, 'name' => 'Sunny', 'type' => 'bird',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 0.18,
                'health_status' => 'recovering', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A small bird currently recovering under veterinary supervision.',
                'story' => 'Sunny was rescued with a minor injury and is receiving supportive care.',
            ],
            [
                'id' => 6, 'name' => 'Oliver', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 3.80,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A playful orange cat that responds well to people.',
                'story' => 'Oliver was rescued as a young cat and has adapted well to shelter life.',
            ],
            [
                'id' => 7, 'name' => 'Max', 'type' => 'dog',
                'gender' => 'male', 'age' => 5, 'size' => 'large', 'weight' => 25.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'sponsored', 'is_urgent' => false,
                'description' => 'A friendly dog with a confident and social personality.',
                'story' => 'Max was rescued from a difficult environment and is now receiving stable care.',
            ],
            [
                'id' => 8, 'name' => 'Coco', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 1.60,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A gentle rabbit that prefers a quiet and safe environment.',
                'story' => 'Coco was found outdoors and brought to the shelter for protection.',
            ],
            [
                'id' => 9, 'name' => 'Robin', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 1, 'size' => 'small', 'weight' => 0.09,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small active bird that requires a secure enclosure.',
                'story' => 'Robin was rescued after being found unable to return safely to its habitat.',
            ],
            [
                'id' => 10, 'name' => 'Luna', 'type' => 'cat',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 4.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A curious cat that enjoys attention and interactive play.',
                'story' => 'Luna was rescued from an unsafe area and has become comfortable around people.',
            ],

            // 11 - 20
            [
                'id' => 11, 'name' => 'Daisy', 'type' => 'dog',
                'gender' => 'female', 'age' => 2, 'size' => 'medium', 'weight' => 14.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A gentle young dog with a friendly personality.',
                'story' => 'Daisy was rescued from the street and is ready for adoption.',
            ],
            [
                'id' => 12, 'name' => 'Bunny', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.20,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small white rabbit with a gentle temperament.',
                'story' => 'Bunny was surrendered by a family and is looking for a responsible adopter.',
            ],
            [
                'id' => 13, 'name' => 'Sky', 'type' => 'bird',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 0.15,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A colorful small bird with an active personality.',
                'story' => 'Sky was rescued and evaluated by the veterinary team before joining the adoption program.',
            ],
            [
                'id' => 14, 'name' => 'Diva', 'type' => 'cat',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 2.80,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young cat that is playful and comfortable around people.',
                'story' => 'Diva was rescued at a young age and has received routine veterinary care.',
            ],
            [
                'id' => 15, 'name' => 'Bruno', 'type' => 'dog',
                'gender' => 'male', 'age' => 6, 'size' => 'large', 'weight' => 28.00,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => true,
                'description' => 'A large dog recovering from a previous injury.',
                'story' => 'Bruno was rescued after an accident and is currently receiving veterinary treatment.',
            ],
            [
                'id' => 16, 'name' => 'Simba', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 4.30,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young orange cat with a confident and affectionate personality.',
                'story' => 'Simba was rescued from a crowded area and is now ready for adoption.',
            ],
            [
                'id' => 17, 'name' => 'Hazel', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 2, 'size' => 'medium', 'weight' => 1.80,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm rabbit that enjoys a quiet environment.',
                'story' => 'Hazel was rescued from an outdoor location and brought to safety.',
            ],
            [
                'id' => 18, 'name' => 'Blue', 'type' => 'bird',
                'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 0.11,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small blue bird with an active and alert personality.',
                'story' => 'Blue was rescued after being found injured and has since recovered.',
            ],
            [
                'id' => 19, 'name' => 'Nala', 'type' => 'cat',
                'gender' => 'female', 'age' => 4, 'size' => 'medium', 'weight' => 4.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly cat that enjoys human company.',
                'story' => 'Nala was rescued from the street and completed her veterinary assessment.',
            ],
            [
                'id' => 20, 'name' => 'Hazel', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.30,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young rabbit that needs a safe indoor environment.',
                'story' => 'This rabbit was found outdoors and brought to the shelter for protection.',
            ],

            // 21 - 30
            [
                'id' => 21, 'name' => 'Pico', 'type' => 'bird',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 0.10,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small bird that is active and alert.',
                'story' => 'Pico was rescued and placed under observation before becoming available.',
            ],
            [
                'id' => 22, 'name' => 'Pepper', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 2, 'size' => 'small', 'weight' => 0.08,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small dark-colored bird requiring a secure enclosure.',
                'story' => 'Pepper was rescued after being found in an unsafe location.',
            ],
            [
                'id' => 23, 'name' => ' silky', 'type' => 'cat',
                'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 2.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young playful cat that enjoys interaction.',
                'story' => ' silky was rescued as a kitten and has grown into a healthy young cat.',
            ],
            [
                'id' => 24, 'name' => 'Cooper', 'type' => 'dog',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 13.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A gentle dog that adapts well to family environments.',
                'story' => 'Cooper was rescued and socialized with people before entering the adoption program.',
            ],
            [
                'id' => 25, 'name' => 'Pearl', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.25,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A white rabbit with a calm and gentle temperament.',
                'story' => 'Pearl was surrendered and is waiting for a suitable permanent home.',
            ],
            [
                'id' => 26, 'name' => 'Ruby', 'type' => 'bird',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 0.14,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A colorful bird with an active personality.',
                'story' => 'Ruby was rescued and examined before being placed in the adoption program.',
            ],
            [
                'id' => 27, 'name' => 'Tiger', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.70,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A long-haired cat recovering under veterinary supervision.',
                'story' => 'Tiger was rescued in poor condition and is receiving treatment before adoption.',
            ],
            [
                'id' => 28, 'name' => 'Bella', 'type' => 'cat',
                'gender' => 'female', 'age' => 2, 'size' => 'medium', 'weight' => 3.90,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly white cat that enjoys human attention.',
                'story' => 'Bella was rescued from an unsafe area and is now ready for a permanent home.',
            ],
            [
                'id' => 29, 'name' => 'Charlie', 'type' => 'dog',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 12.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly young dog with a gentle temperament.',
                'story' => 'Charlie was rescued from the street and has responded well to socialization.',
            ],
            [
                'id' => 30, 'name' => 'Cloud', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.10,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small white rabbit that needs a calm home.',
                'story' => 'Cloud was rescued from an outdoor area and is now safely cared for.',
            ],

            // 31 - 40
            [
                'id' => 31, 'name' => 'Sparrow', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 1, 'size' => 'small', 'weight' => 0.07,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small rescued bird requiring a safe enclosure.',
                'story' => 'Sparrow was brought to the shelter after being found in an unsafe area.',
            ],
            [
                'id' => 32, 'name' => 'Mochi', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.10,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A playful cat that enjoys climbing and exploring.',
                'story' => 'Mochi was rescued from an abandoned building and has adapted well to shelter care.',
            ],
            [
                'id' => 33, 'name' => 'Leo', 'type' => 'dog',
                'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 7.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young puppy that is energetic and social.',
                'story' => 'Leo was rescued as a puppy and is currently looking for a responsible family.',
            ],
            [
                'id' => 34, 'name' => 'Willow', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 2, 'size' => 'medium', 'weight' => 1.70,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm rabbit that prefers a peaceful environment.',
                'story' => 'Willow was rescued from an outdoor location and is now safely housed.',
            ],
            [
                'id' => 35, 'name' => 'Finch', 'type' => 'bird',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 0.09,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small colorful bird with an active personality.',
                'story' => 'Finch was rescued and placed under care until a suitable home became available.',
            ],
            [
                'id' => 36, 'name' => 'Garfield', 'type' => 'cat',
                'gender' => 'male', 'age' => 5, 'size' => 'medium', 'weight' => 5.20,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A long-haired cat currently recovering from a health issue.',
                'story' => 'Garfield was rescued in need of medical attention and is being monitored by the veterinary team.',
            ],
            [
                'id' => 37, 'name' => 'Toby', 'type' => 'dog',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 8.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small friendly dog that enjoys playing with people.',
                'story' => 'Toby was rescued from the street and successfully completed his initial assessment.',
            ],
            [
                'id' => 38, 'name' => 'Clover', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 1.40,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A gentle rabbit that enjoys a quiet environment.',
                'story' => 'Clover was rescued outdoors and brought to the shelter for safe care.',
            ],
            [
                'id' => 39, 'name' => 'Bluebell', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 1, 'size' => 'small', 'weight' => 0.08,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small active bird requiring a secure environment.',
                'story' => 'Bluebell was rescued and safely transferred to the shelter.',
            ],
            [
                'id' => 40, 'name' => 'Oscar', 'type' => 'cat',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 4.80,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm cat that enjoys resting in quiet spaces.',
                'story' => 'Oscar was rescued from an abandoned property and is ready for adoption.',
            ],

            // 41 - 50
            [
                'id' => 41, 'name' => 'Smokey', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.40,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A playful cat that enjoys attention.',
                'story' => 'Smokey was rescued from the street and has adapted well to shelter life.',
            ],
            [
                'id' => 42, 'name' => 'Shadow', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 4.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A quiet black cat that prefers calm surroundings.',
                'story' => 'Shadow was rescued from an unsafe neighborhood and is now looking for a home.',
            ],
            [
                'id' => 43, 'name' => 'Snowy', 'type' => 'cat',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 2.70,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young white cat with a gentle personality.',
                'story' => 'Snowy was rescued as a kitten and has received routine veterinary care.',
            ],
            [
                'id' => 44, 'name' => 'Cinnamon', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 1.50,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small rabbit with a calm and curious nature.',
                'story' => 'Cinnamon was rescued from an outdoor area and brought to safety.',
            ],
            [
                'id' => 45, 'name' => 'Rocky', 'type' => 'dog',
                'gender' => 'male', 'age' => 5, 'size' => 'medium', 'weight' => 18.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly dog with a confident personality.',
                'story' => 'Rocky was rescued from the street and is ready for a permanent family.',
            ],
            [
                'id' => 46, 'name' => 'Ginger', 'type' => 'cat',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 4.10,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm orange cat that enjoys gentle interaction.',
                'story' => 'Ginger was rescued from an abandoned building and is now ready for adoption.',
            ],
            [
                'id' => 47, 'name' => 'Milo', 'type' => 'dog',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 11.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A playful young dog that enjoys human company.',
                'story' => 'Milo was rescued from the street and has responded well to socialization.',
            ],
            [
                'id' => 48, 'name' => 'Emy', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.20,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small white rabbit with a gentle temperament.',
                'story' => 'Emy was rescued and is now waiting for a responsible adopter.',
            ],
            [
                'id' => 49, 'name' => 'Rex', 'type' => 'dog',
                'gender' => 'male', 'age' => 6, 'size' => 'large', 'weight' => 30.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'sponsored', 'is_urgent' => false,
                'description' => 'A strong and loyal dog with a calm temperament.',
                'story' => 'Rex was rescued after being abandoned and currently has an active sponsor.',
            ],
            [
                'id' => 50, 'name' => 'Salem', 'type' => 'cat',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 4.60,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm cat that enjoys quiet spaces.',
                'story' => 'Salem was rescued from an unsafe location and has completed his health assessment.',
            ],

            // 51 - 60
            [
                'id' => 51, 'name' => 'Maple', 'type' => 'cat',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 3.20,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young cat with a gentle and curious personality.',
                'story' => 'Maple was rescued from the street and is now ready for adoption.',
            ],
            [
                'id' => 52, 'name' => 'Ash', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.50,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A cat recovering from a previous health issue.',
                'story' => 'Ash was rescued in need of medical care and is currently being monitored.',
            ],
            [
                'id' => 53, 'name' => 'Snowball', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 4.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly white cat that enjoys human attention.',
                'story' => 'Snowball was rescued from an unsafe area and is now available for adoption.',
            ],
            [
                'id' => 54, 'name' => 'Misty', 'type' => 'cat',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 4.20,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A fluffy cat with a calm and affectionate temperament.',
                'story' => 'Misty was rescued and successfully adapted to the shelter environment.',
            ],
            [
                'id' => 55, 'name' => 'Jet', 'type' => 'dog',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 15.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young black dog with an energetic personality.',
                'story' => 'Jet was rescued from the street and is ready for an active family.',
            ],
            [
                'id' => 56, 'name' => 'Jack', 'type' => 'dog',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 16.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly dog that enjoys outdoor activities.',
                'story' => 'Jack was rescued from an abandoned area and has completed his health check.',
            ],
            [
                'id' => 57, 'name' => 'Bruno Jr', 'type' => 'dog',
                'gender' => 'male', 'age' => 5, 'size' => 'medium', 'weight' => 14.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm dog that is comfortable around people.',
                'story' => 'Bruno Jr was rescued and socialized before becoming available for adoption.',
            ],
            [
                'id' => 58, 'name' => 'Simba Jr', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 4.30,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A playful orange cat with an affectionate nature.',
                'story' => 'Simba Jr was rescued from the street and is now ready for adoption.',
            ],
            [
                'id' => 59, 'name' => 'Mittens', 'type' => 'cat',
                'gender' => 'female', 'age' => 4, 'size' => 'medium', 'weight' => 4.70,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A fluffy cat that enjoys a calm home environment.',
                'story' => 'Mittens was rescued from an abandoned property and is now available for adoption.',
            ],
            [
                'id' => 60, 'name' => 'Biscuit', 'type' => 'dog',
                'gender' => 'male', 'age' => 3, 'size' => 'small', 'weight' => 9.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small friendly dog with a playful personality.',
                'story' => 'Biscuit was rescued from the street and is looking for a loving family.',
            ],

            // 61 - 70
            [
                'id' => 61, 'name' => 'Rusty', 'type' => 'dog',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 13.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm small dog that enjoys human company.',
                'story' => 'Rusty was rescued from an unsafe area and successfully completed his veterinary assessment.',
            ],
            [
                'id' => 62, 'name' => 'Bella Jr', 'type' => 'dog',
                'gender' => 'female', 'age' => 2, 'size' => 'small', 'weight' => 7.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small dog with a gentle and friendly personality.',
                'story' => 'Bella Jr was rescued and is now ready for adoption.',
            ],
            [
                'id' => 63, 'name' => 'Cocoa', 'type' => 'dog',
                'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 6.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young puppy that is active and social.',
                'story' => 'Cocoa was rescued as a puppy and is receiving socialization and basic care.',
            ],
            [
                'id' => 64, 'name' => 'Mia', 'type' => 'cat',
                'gender' => 'female', 'age' => 2, 'size' => 'medium', 'weight' => 3.80,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm cat that enjoys resting in quiet spaces.',
                'story' => 'Mia was rescued from the street and has adapted well to shelter life.',
            ],
            [
                'id' => 65, 'name' => 'Loki', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.30,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm gray cat with a curious personality.',
                'story' => 'Loki was rescued from an abandoned property and is now ready for adoption.',
            ],
            [
                'id' => 66, 'name' => 'Tiger Jr', 'type' => 'cat',
                'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 2.60,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young tabby cat that is playful and curious.',
                'story' => 'Tiger Jr was rescued as a kitten and has received routine veterinary care.',
            ],
            [
                'id' => 67, 'name' => 'Felix', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 4.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A black-and-white cat with a friendly temperament.',
                'story' => 'Felix was rescued from the street and is now ready for a permanent home.',
            ],
            [
                'id' => 68, 'name' => 'Harley', 'type' => 'dog',
                'gender' => 'female', 'age' => 5, 'size' => 'medium', 'weight' => 16.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly dog that enjoys resting and spending time with people.',
                'story' => 'Harley was rescued after being abandoned and has settled well into shelter care.',
            ],
            [
                'id' => 69, 'name' => 'Oreo', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 3.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A playful black-and-white cat.',
                'story' => 'Oreo was rescued from an unsafe area and is ready for adoption.',
            ],
            [
                'id' => 70, 'name' => 'Ash Jr', 'type' => 'cat',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 4.80,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm gray cat that prefers a peaceful environment.',
                'story' => 'Ash Jr was rescued from the street and has completed his health assessment.',
            ],

            // 71 - 80
            [
                'id' => 71, 'name' => 'Shadow Jr', 'type' => 'dog',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 15.50,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => true,
                'description' => 'A dog recovering from an injury and receiving veterinary care.',
                'story' => 'Shadow Jr was rescued in poor condition and is currently under treatment.',
            ],
            [
                'id' => 72, 'name' => 'Duke', 'type' => 'dog',
                'gender' => 'male', 'age' => 6, 'size' => 'large', 'weight' => 27.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm mature dog with a loyal personality.',
                'story' => 'Duke was rescued from the street and is looking for an experienced adopter.',
            ],
            [
                'id' => 73, 'name' => 'Thor', 'type' => 'cat',
                'gender' => 'male', 'age' => 4, 'size' => 'large', 'weight' => 5.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A large long-haired cat with a calm personality.',
                'story' => 'Thor was rescued and is now comfortable in human care.',
            ],
            [
                'id' => 74, 'name' => 'nicy', 'type' => 'cat',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 4.10,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A rescued cat currently receiving veterinary support.',
                'story' => 'nicy was brought to the shelter in need of care and is progressing well.',
            ],
            [
                'id' => 75, 'name' => 'Snowflake', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 1, 'size' => 'small', 'weight' => 0.10,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small white bird that requires a secure and warm environment.',
                'story' => 'Snowflake was rescued and is now safely cared for at the shelter.',
            ],
            [
                'id' => 76, 'name' => 'Storm', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 2, 'size' => 'small', 'weight' => 0.20,
                'health_status' => 'injured', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'under_treatment', 'is_urgent' => true,
                'description' => 'A rescued bird recovering from an injury.',
                'story' => 'Storm was found injured and is currently receiving veterinary treatment.',
            ],
            [
                'id' => 77, 'name' => 'Pip', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 1, 'size' => 'small', 'weight' => 0.08,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small active bird that enjoys a secure enclosure.',
                'story' => 'Pip was rescued and transferred to the shelter for safe care.',
            ],
            [
                'id' => 78, 'name' => 'Jack Sparrow', 'type' => 'bird',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 0.11,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small rescued bird with an alert personality.',
                'story' => 'Jack Sparrow was rescued from an unsafe location and is now under stable care.',
            ],
            [
                'id' => 79, 'name' => 'Caramel', 'type' => 'rabbit',
                'gender' => 'male', 'age' => 2, 'size' => 'small', 'weight' => 1.50,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A brown rabbit with a calm temperament.',
                'story' => 'Caramel was rescued outdoors and brought to the shelter for protection.',
            ],
            [
                'id' => 80, 'name' => 'Mocha', 'type' => 'dog',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 14.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm dog that enjoys quiet surroundings.',
                'story' => 'Mocha was rescued from the street and is ready for adoption.',
            ],

            // 81 - 90
            [
                'id' => 81, 'name' => 'Zoe', 'type' => 'dog',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 13.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly dog with an energetic personality.',
                'story' => 'Zoe was rescued and successfully completed her veterinary assessment.',
            ],
            [
                'id' => 82, 'name' => 'Charlie Vet', 'type' => 'dog',
                'gender' => 'male', 'age' => 5, 'size' => 'large', 'weight' => 24.00,
                'health_status' => 'sick', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => true,
                'description' => 'A dog currently receiving professional veterinary treatment.',
                'story' => 'Charlie was rescued and transferred directly to veterinary care for evaluation.',
            ],
            [
                'id' => 83, 'name' => 'Hazel Jr', 'type' => 'rabbit',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 1.80,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A calm brown rabbit that needs a safe home.',
                'story' => 'Hazel Jr was rescued and is now ready for adoption.',
            ],
            [
                'id' => 84, 'name' => 'Ruby Jr', 'type' => 'rabbit',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 1.30,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young rabbit with a curious personality.',
                'story' => 'Ruby Jr was rescued from an outdoor location and brought to safety.',
            ],
            [
                'id' => 85, 'name' => 'Lucky', 'type' => 'dog',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 12.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A rescued dog with an active and friendly personality.',
                'story' => 'Lucky was rescued from the street and is now looking for a permanent home.',
            ],
            [
                'id' => 86, 'name' => 'Lily', 'type' => 'cat',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 3.90,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A fluffy cat with a calm and affectionate nature.',
                'story' => 'Lily was rescued from an unsafe area and has adapted well to shelter life.',
            ],
            [
                'id' => 87, 'name' => 'Blue Jay', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 1, 'size' => 'small', 'weight' => 0.09,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small active bird requiring a secure enclosure.',
                'story' => 'Blue Jay was rescued and is now safely cared for at the shelter.',
            ],
            [
                'id' => 88, 'name' => 'Raven', 'type' => 'bird',
                'gender' => 'unknown', 'age' => 2, 'size' => 'small', 'weight' => 0.12,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A rescued bird that requires a safe and suitable enclosure.',
                'story' => 'Raven was rescued after being found in an unsafe environment.',
            ],
            [
                'id' => 89, 'name' => 'Bobby', 'type' => 'dog',
                'gender' => 'male', 'age' => 5, 'size' => 'medium', 'weight' => 15.50,
                'health_status' => 'sick', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => true,
                'description' => 'A dog receiving treatment for a current health condition.',
                'story' => 'Bobby was rescued in poor health and is currently under veterinary supervision.',
            ],
            [
                'id' => 90, 'name' => 'Tom', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.10,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly cat with a calm personality.',
                'story' => 'Tom was rescued from the street and is now ready for adoption.',
            ],

            // 91 - 100
            [
                'id' => 91, 'name' => 'Teddy', 'type' => 'dog',
                'gender' => 'male', 'age' => 1, 'size' => 'small', 'weight' => 5.50,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young puppy with a gentle and playful personality.',
                'story' => 'Teddy was rescued as a puppy and is now looking for a loving family.',
            ],
            [
                'id' => 92, 'name' => 'Sam', 'type' => 'dog',
                'gender' => 'male', 'age' => 3, 'size' => 'small', 'weight' => 8.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A small dog with a calm temperament.',
                'story' => 'Sam was rescued from the street and has completed his initial veterinary check.',
            ],
            [
                'id' => 93, 'name' => 'Cleo', 'type' => 'cat',
                'gender' => 'female', 'age' => 3, 'size' => 'medium', 'weight' => 4.00,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A friendly cat that enjoys exploring safe outdoor-like spaces.',
                'story' => 'Cleo was rescued and successfully adapted to human care.',
            ],
            [
                'id' => 94, 'name' => 'Lynx', 'type' => 'other',
                'gender' => 'unknown', 'age' => 4, 'size' => 'large', 'weight' => 18.00,
                'health_status' => 'healthy', 'is_vaccinated' => false, 'is_neutered' => false,
                'availability_status' => 'sponsored', 'is_urgent' => false,
                'description' => 'A rescued wild feline currently under specialized care.',
                'story' => 'Lynx was transferred to a protected care environment and is not part of regular adoption.',
            ],
            [
                'id' => 95, 'name' => 'Mango', 'type' => 'cat',
                'gender' => 'male', 'age' => 2, 'size' => 'medium', 'weight' => 3.80,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A young orange cat with a gentle personality.',
                'story' => 'Mango was rescued from an unsafe area and is ready for adoption.',
            ],
            [
                'id' => 96, 'name' => 'Bear', 'type' => 'dog',
                'gender' => 'male', 'age' => 4, 'size' => 'medium', 'weight' => 17.00,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A rescued dog recovering under veterinary supervision.',
                'story' => 'Bear was rescued after an injury and is progressing with treatment.',
            ],
            [
                'id' => 97, 'name' => 'Ivory', 'type' => 'cat',
                'gender' => 'female', 'age' => 1, 'size' => 'small', 'weight' => 2.40,
                'health_status' => 'recovering', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'under_treatment', 'is_urgent' => false,
                'description' => 'A young cat recovering after a recent medical procedure.',
                'story' => 'Ivory was rescued and is currently receiving post-treatment care.',
            ],
            [
                'id' => 98, 'name' => 'Nero', 'type' => 'cat',
                'gender' => 'male', 'age' => 3, 'size' => 'medium', 'weight' => 4.20,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A confident cat that enjoys attention and comfortable spaces.',
                'story' => 'Nero was rescued and successfully completed his health assessment.',
            ],
            [
                'id' => 99, 'name' => 'Ace', 'type' => 'dog',
                'gender' => 'male', 'age' => 5, 'size' => 'large', 'weight' => 23.00,
                'health_status' => 'sick', 'is_vaccinated' => true, 'is_neutered' => true,
                'availability_status' => 'under_treatment', 'is_urgent' => true,
                'description' => 'A dog currently receiving veterinary treatment and monitoring.',
                'story' => 'Ace was rescued and transferred to veterinary care for further examination.',
            ],
            [
                'id' => 100, 'name' => 'Hope', 'type' => 'cat',
                'gender' => 'female', 'age' => 2, 'size' => 'medium', 'weight' => 3.70,
                'health_status' => 'healthy', 'is_vaccinated' => true, 'is_neutered' => false,
                'availability_status' => 'available', 'is_urgent' => false,
                'description' => 'A rescued cat with a gentle and hopeful personality.',
                'story' => 'Hope was rescued from an unsafe environment and is waiting for a caring home.',
            ],
        ];

        DB::transaction(function () use ($animals) {

            foreach ($animals as $data) {

                $animalId = $data['id'];

                unset($data['id']);

                // إنشاء الحيوان بالـID المطابق لمجلد الصورة
                $animal = Animal::withTrashed()->where('id', $animalId)->first();

                if ($animal) {
                    if ($animal->trashed()) {
                        $animal->restore();
                    }

                    $animal->update($data);
                } else {
                    $animal = new Animal();
                    $animal->id = $animalId;
                    $animal->fill($data);
                    $animal->save();
                }

                /*
                 * الصورة:
                 * storage/app/public/animals/{id}/photo.avif
                 */
                $photoPath = "animals/{$animalId}/photo.avif";

                if (Storage::disk('public')->exists($photoPath)) {

                    AnimalPhoto::updateOrCreate(
                        [
                            'animal_id' => $animal->id,
                            'is_main' => true,
                        ],
                        [
                            'photo_url' => $photoPath,
                            'is_main' => true,
                            'order_number' => 1,
                        ]
                    );
                }
            }
        });

        $this->command->info('100 animals seeded successfully.');
        $this->command->info('Animal photos linked from storage/app/public/animals/{id}/photo.avif');
    }
}
