<?php

namespace App\Controller\Admin;

use App\Entity\Meetup;
use App\Entity\Price;
use App\Exception\DuplicatedException;
use App\Form\PriceFormType;
use App\Manager\MeetupManager;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NullFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

class AdminMeetupCrudController extends AbstractCrudController
{
    private $eventManager;

    public function __construct()
    {
    }

    public static function getEntityFqcn(): string
    {
        return Meetup::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Evénement')
            ->setEntityLabelInPlural('Evénements')
            ->setPageTitle(Crud::PAGE_INDEX, 'Liste des meetups')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le meetup')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du meetup')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un meetup')
            ->setSearchFields(['name', 'image', 'content', 'tags'])
            ->setDefaultSort(['created_at' => 'DESC'])
            ->setTimezone('Europe/Paris');
    }

    public function configureFields(string $pageName): iterable
    {
        // General information
        $generalPanel = FormField::addPanel('Informations générales')->setIcon('fa fa-pencil');
        $name = TextField::new('name', 'Nom du meetup');
        $content = TextEditorField::new('content', 'Description');
        $location = TextField::new('location', 'Lieu')->setRequired(true);
        $startDate = DateTimeField::new('start_date', 'Date de début')->setTimezone('Europe/Paris');
        $endDate = DateTimeField::new('end_date', 'Date de fin')->setTimezone('Europe/Paris');
        $tags = ArrayField::new('tags');
        $externalURL = UrlField::new('external_url', 'Lien externe');
        
        // Price
        $pricePanel = FormField::addPanel('Prix')->setIcon('fa fa-money');
        $price = CollectionField::new('price', 'Prix')
                                    ->allowAdd() 
                                    ->allowDelete()
                                    ->setEntryIsComplex(true)
                                    ->setEntryType(PriceFormType::class)
                                    ->setFormTypeOptions([
                                        'by_reference' => 'false' 
                                    ]);
        $isFree = BooleanField::new('is_free', 'Gratuit ?');

        // Publishing
        $publishPanel = FormField::addPanel('Prix')->setIcon('fa fa-money');
        $isPublished = BooleanField::new('is_published', 'Publié ?');

        // Date format for index pages
        $dateFormat = 'dd MMMM yyyy hh:mm';

        switch($pageName){
            case Crud::PAGE_INDEX:
                return [$name, $location, $startDate->setFormat($dateFormat), $endDate->setFormat($dateFormat), $isFree];
                break;
            case Crud::PAGE_DETAIL:
                return [$name, $content, $location, $startDate, $tags];
                break;
            case Crud::PAGE_NEW:
            case Crud::PAGE_EDIT:
                return [$generalPanel, $name, $content, $location, $startDate, $endDate, $tags, $externalURL, $pricePanel, $price, $isFree, $publishPanel, $isPublished];
                break;
            default:
                return null;
        }
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('name')
            ->add('start_date')
            ->add('location');
    }

    public function configureActions(Actions $actions): Actions
    {
        $newAction = function (Action $action) {
            return $action->setLabel('Ajouter un meetup');
        };
        $saveAction = function (Action $action) {
            return $action->setLabel('Sauvegarder le meetup')->addCssClass('btn-success');
        };

        return $actions
            ->update(Crud::PAGE_INDEX, Action::NEW, $newAction)
            ->update(Crud::PAGE_NEW, Action::SAVE_AND_RETURN, $saveAction);
    }   
}