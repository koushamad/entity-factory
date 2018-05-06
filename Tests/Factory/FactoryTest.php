<?php

namespace lkovace18\EntityFactoryBundle\Tests\Factory;

use Doctrine\ORM\Tools\SchemaTool;
use lkovace18\EntityFactoryBundle\Factory\Factory;
use lkovace18\EntityFactoryBundle\Tests\Dummy\app\AppKernel;
use lkovace18\EntityFactoryBundle\Tests\Dummy\TestCase;
use lkovace18\EntityFactoryBundle\Tests\Dummy\TestEntity\Address;
use lkovace18\EntityFactoryBundle\Tests\Dummy\TestEntity\App;
use lkovace18\EntityFactoryBundle\Tests\Dummy\TestEntity\Phone;
use lkovace18\EntityFactoryBundle\Tests\Dummy\TestEntity\Treehouse;
use lkovace18\EntityFactoryBundle\Tests\Dummy\TestEntity\User;

class FactoryTest extends TestCase
{
    /** @test */
    public function it_can_be_retrieved_from_the_service_container()
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();

       // $factory = $kernel->getContainer()->get('entity_factory');
      //  $this->assertInstanceOf(Factory::class, $factory);
    }

    /** @test */
    public function it_creates_an_entity()
    {
        $address = $this->factory->make(Address::class);

        $this->assertInstanceOf(Address::class, $address);
    }

    /** @test */
    public function it_persists_an_entity()
    {
        $this->factory->create(Address::class, [
            'street' => 'Main St. 10',
            'city'   => 'New York',
            'zip'    => '82020',
        ]);

        $this->seeInDatabase(Address::class, [
            'street' => 'Main St. 10',
        ]);
    }

    /** @test */
    public function it_persists_an_entity_with_inheritance_mapping()
    {
        $this->factory->create(Treehouse::class, [
            'treeType' => 'oak',
        ]);

        $this->seeInDatabase(Treehouse::class, [
            'treeType' => 'oak',
        ]);
    }

    /** @test */
    public function it_sets_id_field_if_not_auto_generated()
    {
        $this->factory->create(Address::class, ['id' => 10]);

        $this->seeInDatabase(Address::class, ['id' => 10]);
    }

    /** @test */
    public function it_creates_multiple_entities()
    {
        $users = $this->factory->times(3)->make(User::class);

        $this->assertEquals(3, count($users));
    }

    /** @test */
    public function it_persists_multiple_entities()
    {
        $this->factory->times(3)->create(Address::class, [
            'street' => 'Main St. 10',
            'city'   => 'New York',
            'zip'    => '82020',
        ])
        ;

        $count = $this->getDatabaseCount(Address::class, []);

        $this->assertEquals(3, $count);
    }

    /** @test */
    public function it_returns_fake_data_for_an_entity()
    {
        $values = $this->factory->values(Address::class);

        $this->assertNotNull($values['street']);
        $this->assertNotNull($values['city']);
        $this->assertNotNull($values['zip']);
    }

    /** @test */
    public function it_returns_fake_data_multiple_times()
    {
        $values = $this->factory->times(2)->values(Address::class);

        $this->assertEquals(2, count($values));
        $this->assertNotNull($values[0]['street']);
        $this->assertNotNull($values[0]['city']);
        $this->assertNotNull($values[0]['zip']);
        $this->assertNotNull($values[1]['street']);
        $this->assertNotNull($values[1]['city']);
        $this->assertNotNull($values[1]['zip']);
    }

    /** @test */
    public function it_returns_fake_data_for_associtations()
    {
        $values = $this->factory->values(User::class);

        $this->assertEquals(5, count($values['address']));
        $this->assertNotNull($values['address']['street']);
    }

    /** @test */
    public function it_allows_to_override_fake_values()
    {
        $values = $this->factory->values(Address::class, ['zip' => '01097']);

        $this->assertEquals('01097', $values['zip']);
    }

    /** @test */
    public function it_adds_fake_data_from_config_files()
    {
        $address = $this->factory->create(Address::class);

        $this->assertNotNull($address->getStreet());
        $this->assertNotNull($address->getCity());
        $this->assertNotNull($address->getZip());
    }

    /** @test */
    public function it_adds_fake_data_to_associated_entites()
    {
        $user = $this->factory->create(User::class);
        $address = $user->getAddress();

        $this->assertNotNull($user->getFirstName());
        $this->assertNotNull($address->getStreet());
        $this->assertNotNull($address->getCity());
        $this->assertNotNull($address->getZip());
    }

    /** @test */
    public function it_generates_different_data_when_multiple_entities_are_generated()
    {
        $addresses = $this->factory->times(2)->make(Address::class);

        /** @var Address $address1 */
        $address1 = $addresses[0];

        /** @var Address $address2 */
        $address2 = $addresses[1];

        $data1 = [
            $address1->getStreet(),
            $address1->getCity(),
            $address1->getZip(),
        ];

        $data2 = [
            $address2->getStreet(),
            $address2->getCity(),
            $address2->getZip(),
        ];

        $this->assertNotEquals($data1, $data2);
    }

    /** @test */
    public function it_persists_deeply_nested_associations()
    {
        $user = $this->factory->create(User::class);

        /** @var Phone $phone */
        $phone = $user->getPhone();

        /** @var Apps $app */
        $app = $phone->getApps();

        $this->assertEquals(1, $phone->getApps()->count());
        $this->assertNotNull($app->first()->getTitle());
    }

    /** @test */
    public function it_creates_multiple_associations()
    {
        $apps = $this->factory->times(5)->create(App::class);
        $phone = $this->factory->create(Phone::class, ['apps' => $apps]);

        $this->assertEquals(5, $phone->getApps()->count());
        foreach ($phone->getApps() as $app) {
            $this->assertInstanceOf(App::class, $app);
        }
    }

    /** @test */
    public function it_attaches_entities_for_many_to_many_owning_side()
    {
        $this->markTestSkipped('Wip');
    }

    /** @test */
    public function it_attaches_entities_for_many_to_many_inverse_side()
    {
        $this->markTestSkipped('Wip');
    }

    /** @test */
    public function it_allows_to_set_another_entity_manager()
    {
        $kernel = new AppKernel('test', true);
        $kernel->boot();

        $anotherEntityManager = $kernel->getContainer()->get('doctrine.orm.entity_manager');

        $tool = new SchemaTool($anotherEntityManager);
        $tool->createSchema($anotherEntityManager->getMetadataFactory()->getAllMetadata());

        $this->factory->setEntityManager($anotherEntityManager);
        $address = $this->factory->create(Address::class);

        $this->assertFalse($this->em->contains($address));
        $this->assertTrue($anotherEntityManager->contains($address));
    }
}
