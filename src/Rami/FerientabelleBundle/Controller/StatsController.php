<?php

namespace Rami\FerientabelleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Rami\FerientabelleBundle\Entity\User;
use Rami\FerientabelleBundle\Entity\Timeframe;
use Rami\FerientabelleBundle\Form\Type\TimeframeType;
use Rami\FerientabelleBundle\Form\Type\FriendType;
use Rami\FerientabelleBundle\Form\Type\SettingsType;

class StatsController extends Controller
{
    
    public function statsAction()
    {
		$users_total = 0;
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:User');
		$builder = $repository->createQueryBuilder('u');
		$query = $builder
					->select('COUNT(u.id)') 
					->getQuery();

		$users_total = $query->getSingleScalarResult();
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:Timeframe');
		$builder = $repository->createQueryBuilder('t');
		$query = $builder
					->select('COUNT(t.id)') 
					->getQuery();

		$tf_total = $query->getSingleScalarResult();
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:Timeframe');
		$builder = $repository->createQueryBuilder('t');
		$query = $builder
					->select('AVG(DATE_DIFF(t.to, t.from))') 
					->getQuery();

		$tf_avg_l = $query->getSingleScalarResult();
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:Timeframe');
		$builder = $repository->createQueryBuilder('t');
		$query = $builder
					->select('MAX(t.to)') 
					->getQuery();

		$tf_max = $query->getSingleScalarResult();
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:Timeframe');
		$builder = $repository->createQueryBuilder('t');
		$query = $builder
					->select('SUM(DATE_DIFF(t.to, t.from))') 
					->where('t.availability = -1')
					->getQuery();

		$tf_l_m1 = $query->getSingleScalarResult();
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:Timeframe');
		$builder = $repository->createQueryBuilder('t');
		$query = $builder
					->select('SUM(DATE_DIFF(t.to, t.from))') 
					->where('t.availability = 1')
					->getQuery();

		$tf_l_1 = $query->getSingleScalarResult();
		
		$repository = $this->getDoctrine()->getRepository('RamiFerientabelleBundle:Timeframe');
		$builder = $repository->createQueryBuilder('t');
		$query = $builder
					->select('SUM(DATE_DIFF(t.to, t.from))') 
					->where('t.availability = 0')
					->getQuery();

		$tf_l_0 = $query->getSingleScalarResult();
		
        return $this->render('RamiFerientabelleBundle:Stats:stats.html.twig', array(
			'users_total' => $users_total,
			'tf_total' => $tf_total,
			'tf_avg_l' => $tf_avg_l,
			'tf_l_1' => $tf_l_1,
			'tf_l_m1' => $tf_l_m1,
			'tf_l_0' => $tf_l_0,
			'tf_max' => $tf_max,
        ));
    }
}
