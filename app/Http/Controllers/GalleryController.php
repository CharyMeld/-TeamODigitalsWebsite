<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index()
    {
        // Images located in storage/gallery directory
        $images = [
            [
                'path' => 'storage/gallery/project1.jpg',
                'title' => 'Business Consulting Project',
                'description' => 'Optimized business strategy and process automation.',
                'category' => 'consulting',
            ],
            [
                'path' => 'storage/gallery/project2.jpg',
                'title' => 'Data Digitization Case',
                'description' => 'Converting legacy records into secure digital formats.',
                'category' => 'digitization',
            ],
            [
                'path' => 'storage/gallery/project3.jpg',
                'title' => 'Custom Software Development',
                'description' => 'Developed a scalable SaaS platform.',
                'category' => 'software',
            ],
            [
                'path' => 'storage/gallery/project4.jpg',
                'title' => 'IT Support Migration',
                'description' => 'Seamless transition to cloud infrastructure.',
                'category' => 'it-support',
            ],
        ];

        $showcaseProjects = [
            [
                'image' => 'storage/gallery/showcase1.jpg',
                'title' => 'Raw Files',
                'description' => 'Before Record Digitization and Digitalization.',
            ],
            [
                'image' => 'storage/gallery/showcase2.jpg',
                'title' => 'Working on the Raw Files',
                'description' => 'Convert physical records into digital assets for easy access and security.',
            ],
            [
                'image' => 'storage/gallery/showcase3.jpg',
                'title' => 'Physical Arrangement after Digitization and Digitilization',
                'description' => 'This is the physical display of document after scanning to aid the location of the physical files.',
            ],
            [
                'image' => 'storage/gallery/showcase4.jpg',
                'title' => 'Working on the Documents in the Database',
                'description' => 'TeamO Staff in the process of uploading the scanned document to database .',
            ],
            [
                'image' => 'storage/gallery/showcase5.jpg',
                'title' => 'Screenshot of the Database',
                'description' => 'This shows the screenshot of the database software that is used to upload the scanned documets.',
            ],
        ];

        return view('gallery', compact('images', 'showcaseProjects'));
    }
}
