<?php

namespace App\DataFixtures;

use DateTime;
use Faker\Factory;
use DateTimeImmutable;
use App\Entity\Article;
use App\Entity\Category;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;


class ArticleFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('FR_fr');
        //créer 3 categories fakées
        for($i = 1; $i<= 3; $i++) {
            $category = new Category();
            $category->setTitle($faker->sentence())
                    ->setDescription($faker->paragraph());
            $manager->persist($category);


            //créer entre et 6 articles:
            for($j = 1; $j <=mt_rand(4, 6); $j++){
                $article=new Article();
                $content = '<p>' . join($faker->paragraphs(5), '</p> <p>') . '</p>';

                $article->setTitle($faker->sentence())
                        ->setContent($faker->paragraphs)
                        ->setImage("http://placehold.it/350x150")
                        ->setCreatedAt(new DateTimeImmutable());
                        $manager->persist($article);
            }
        }
        // $product = new Product();
        // $manager->persist($product);

        $manager->flush();
    }
}
