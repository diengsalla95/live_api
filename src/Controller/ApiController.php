<?php

namespace App\Controller;
use App\Entity\Region;
use App\Repository\RegionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ApiController extends AbstractController
{
    /**
     * @Route("/api/regions/api", name="api_add_region_api",methods={"GET"})
     */
    public function addRegionByApi(SerializerInterface $serializer)
    {
        $regionJson=file_get_contents('https://geo.api.gouv.fr/regions');
        // //METHODE 1
        // //Decode json vers tableau
        // $regionTab=$serializer->decode($regionJson,"json");
        // //Denormalisation Tableau vers objet ou tableau objet
        // $regionObjet=$serializer->denormalize($regionTab,'App\Entity\Region[]');
        
        //METHODE 2:Deserialisation json vers objet ou tableau d'objet
        $regionObjet=$serializer->deserialize($regionJson,'App\Entity\Region[]','json');
        $entityManager=$this->getDoctrine()->getManager();
        foreach($regionObjet as $region){
            $entityManager->persist($region);
        }
        $entityManager->flush();
        return new JsonResponse("success",Response::HTTP_CREATED,[],true);
    }
    /**
     * @Route("/api/regions", name="api_show_region_api",methods={"GET"})
     */
    public function showRegion(SerializerInterface $serializer,RegionRepository $repo)
    {
        $regionObjet=$repo->findAll();
        $regionJson=$serializer->serialize($regionObjet,"json");
        return new JsonResponse($regionJson,Response::HTTP_OK,[],true);
    }
     /**
     * @Route("/api/regions", name="api_add_region_api",methods={"POST"})
     */
    public function addRegion(SerializerInterface $serializer,Request $request,ValidatorInterface $validator)
    {
        //recuperer le contenu du body de la requete
        $regionJson=$request->getContent();
        $region=$serializer->deserialize($regionJson,Region::class,'json');
        //validation
        $error=$validator->validate($region);
        if(count($error)>0){
            $errorString=$serializer->serialize($error,'json');
            return new JsonResponse($errorString,Response::HTTP_BAD_REQUEST,[],true);
        }
        $entityManager=$this->getDoctrine()->getManager();
        $entityManager->persist($region);
        $entityManager->flush();
        return new JsonResponse("success",Response::HTTP_CREATED,[],true);
    }
}
