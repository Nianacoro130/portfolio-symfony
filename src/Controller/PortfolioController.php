<?php

namespace App\Controller;

use App\Entity\Projet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; 
use Doctrine\Persistence\ObjectManager;
use App\Repository\ProjetRepository;
use Knp\Component\Pager\PaginatorInterface;
use App\Form\SearchType;
use Symfony\Component\OptionsResolver\OptionsResolver;


class PortfolioController extends AbstractController
{
    /**
     * @Route("/portfolio", name="portfolio")
     */
    public function index(): Response
    {
        return $this->render('portfolio/index.html.twig', [
            'controller_name' => 'PortfolioController',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('portfolio/home.html.twig');
    }
    
    /**
     * @Route ("/projets",name="ReadProjet")
     */
    public function AffichelesProjets()
    {
        $repo = $this->getDoctrine()->getRepository(Projet::class);

        $projets = $repo->findAll();
        
         return $this->render('portfolio/projets.html.twig',[
          'projets' => $projets
         ]);
        
    }
    
    /**
     * @Route ("/admin/projets/new" , name="modif")
     * @Route("/admin/projets/{id}/edit", name="edit")
     *  
     */
    public function form(Projet $projet= null, Request $request , ObjectManager $manager) 
    {
        if(!$projet){
            $projet = new Projet();
        }
            $form =  $this->createFormBuilder($projet)
            ->add('nom')
            ->add('image')
            ->add('description')
            
            ->getForm();
    
          /*les infos envoyez par les formulaire ce trouve dans la request , on la deja mis en parametre
            formualaire stp essaye d'analyse le requete http que je te passe en paramete ($request)*/          
            $form->handleRequest($request); 
            
            if($form->isSubmitted() && $form->isValid()){
                if(!$projet->getId()){
                 /*car au moment du remplissage  du formulaire la date est null donc:*/
                $projet->setDate(new \DateTime());
                }
                
                $manager->persist($projet);
                $manager->flush();

                return $this->redirectToRoute('detailsprojets',['id'=> $projet->getId()]);
            }
            

             /* ->add('title', TextType::class) pour modifier le type ne pas oublier d'importer les classes*/
            return $this->render('portfolio/create.html.twig',[
            /*la methode createView va permettre de crer un petit objet qui represente un peu
            plus l'affichage du formulaire ($form est un objet complexe c'est pas ce que 
            twig veut avoir ,twig veut le resultat la fonction createview) */
            'formProjet'=> $form->createView(),
            'editMode' => $projet->getId() !== null,
        ]);

    }


    /**
     * @Route("projets/{id}",name="detailsprojets")
     */
    public function DetailsdesProjet($id)
    {
        $repo = $this->getDoctrine()->getRepository(Projet::class);
        $projet = $repo->find($id);
        return $this->render('portfolio/show.html.twig',[
            'projet' => $projet
        ]);

    }


   /**
     * @Route("/recherche", name="search")
     */
    public function recherche(Request $request, ProjetRepository $repo,  PaginatorInterface $paginator) {

        $searchForm = $this->createForm(SearchType::class);
        $searchForm->handleRequest($request);
        
        
        $donnees = $repo->findAll();
 
        if ($searchForm->isSubmitted() && $searchForm->isValid()) {
 
            $nom = $searchForm->getData()->getNom();

            $donnees = $repo->search($nom);


            if ($donnees == null) {
                $this->addFlash('erreur', 'Aucun projet contenant ce mot clé dans le nom n\'a été trouvé, essayez en un autre.');
           
            }

    }

     // Paginate the results of the query
     $projets = $paginator->paginate(
        // Doctrine Query, not results
        $donnees,
        // Define the page parameter
        $request->query->getInt('page', 1),
        // Items per page
        4
    );
    
        return $this->render('portfolio/search.html.twig',[
            'projets' => $projets,
            'searchForm' => $searchForm->createView()
            
        ]);
   
    }


    
}