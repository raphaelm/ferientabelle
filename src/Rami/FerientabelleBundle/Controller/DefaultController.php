<?php

namespace Rami\FerientabelleBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Rami\FerientabelleBundle\Entity\User;
use Rami\FerientabelleBundle\Entity\Timeframe;
use Rami\FerientabelleBundle\Form\Type\TimeframeType;
use Rami\FerientabelleBundle\Form\Type\FriendType;
use Rami\FerientabelleBundle\Form\Type\SettingsType;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('RamiFerientabelleBundle:Default:index.html.twig');
    }   
    
    public function accountAction($id, $public, $private, $errmsg = null, $errmsgf = null)
    {
		$user = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($id);
			
		if (!$user) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if ($user->getPrivatekey() !== $private || $user->getPublickey() !== $public) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		if(false === $this->get('security.context')->isGranted('ROLE_USER') || $this->getUser() == null || $this->getUser()->getId() != $user->getId()) {
			$token = new UsernamePasswordToken($user, null, "secured_area", $user->getRoles());
			$this->get("security.context")->setToken($token);
		}
		
		$new = null;
		$addfriend = $this->createForm(new FriendType(), null);
		
		$ntf = new Timeframe();
		$ntf->setUser($user);
		$nttf = $this->createForm(new TimeframeType(), $ntf);
		
		$settings = $this->createForm(new SettingsType(), $user);
		$settingssaved = false;
		
		if ($this->getRequest()->isMethod('POST') && $errmsgf === null) {
			if($this->getRequest()->query->get('form') == 'addtimeframe'){
				$nttf->bind($this->getRequest());

				if ($nttf->isValid()) {
					$em = $this->getDoctrine()->getManager();
					
					if( $ntf->getFrom() > $ntf->getTo() ) {
						$errmsg = 'Das Startdatum sollte vor dem Enddatum liegen ;-)';
					} else if( $ntf->getFrom()->diff($ntf->getTo())->days > 120 ) {
						$errmsg = 'Dieser Zeitraum ist zu lange ;-) Wenn du keine Ferien hast, erstelle einfach keinen Zeitraum für diese Zeit.';
					} else {
						foreach($user->getTimeframes() as $tf) {
							if( ! ( $ntf->getFrom() > $tf->getTo() || $ntf->getTo() < $tf->getFrom() ) ) {
								$errmsg = 'Dieses Zeitfenster überlappt sich mit einem anderen. Lösche das andere zuerst.';
							}
						}
					}
					if(!$errmsg) {
						$em->persist($ntf);
						$user->addTimeframe($ntf);
						$em->flush();
						$new = $ntf->getId();
						$ntf = new Timeframe();
						$ntf->setUser($user);
						$nttf = $this->createForm(new TimeframeType(), $ntf);
					}
				}
			} else if($this->getRequest()->query->get('form') == 'settings'){
				$em = $this->getDoctrine()->getManager();
				$settings->bind($this->getRequest());
				if ($settings->isValid()) {
					$em->flush();
					$settingssaved = true;
				}
			}
		}
		
		$table = $this->drawTable($user->getFriends(), $user);
		
        return $this->render('RamiFerientabelleBundle:Default:account.html.twig', 
				array(
					'user' => $user,
					'ntff' => $nttf->createView(),
					'errmsg' => $errmsg,
					'errmsgf' => $errmsgf,
					'table' => $table,
					'addfriend' => $addfriend->createView(),
					'new' => $new,
					'settings' => $settings->createView(),
					'settingssaved' => $settingssaved,
				));
    }
    
    private function drawTable($fri, $user = false) {
		
		$dates = array();
		$friends = array();
		$avail = array();
		$rawavail = array();
		// Start date
		$date = new \DateTime();
		$today = new \DateTime();
		// End date
		$end_date = new \DateTime('+6 months');
		$first_day = $end_date;
		$last_day = $date;
	 
		if(($f = $user) !== false) {
			$friends[$f->getId()] = $f;
			$avail[$f->getId()] = array();
			$rawavail[$f->getId()] = array();
			foreach($f->getTimeframes() as $tf) {
				if($tf->getTo() < $today) continue;
				$begin    = $tf->getFrom();
				$end      = clone $tf->getTo();
				$end->add(\DateInterval::createFromDateString('1 day'));
				$interval = \DateInterval::createFromDateString('1 day');
				$days     = new \DatePeriod($begin, $interval, $end);
				$num = 0;
				foreach ( $days as $day ) {
					if($day < $first_day) $first_day = $day;
					if($day > $last_day) $last_day = $day;
					$num++;
					$rawavail[$f->getId()][$day->format('Y-m-d')] = $tf->getAvailability();
				}
				$avail[$f->getId()][$begin->format('Y-m-d')] = array($tf->getAvailability(), $num, $tf->getComment());
			}
		}
		foreach($fri as $f) {
			$friends[$f->getId()] = $f;
			$avail[$f->getId()] = array();
			foreach($f->getTimeframes() as $tf) {
				if($tf->getTo() < $today) continue;
				$begin    = $tf->getFrom();
				$end      = clone $tf->getTo();
				$end->add(\DateInterval::createFromDateString('1 day'));
				$interval = \DateInterval::createFromDateString('1 day');
				$days     = new \DatePeriod($begin, $interval, $end);
				$num = 0;
				foreach ( $days as $day ) {
					if($day < $first_day) $first_day = $day;
					if($day > $last_day) $last_day = $day;
					$num++;
					$rawavail[$f->getId()][$day->format('Y-m-d')] = $tf->getAvailability();
				}
				$avail[$f->getId()][$begin->format('Y-m-d')] = array($tf->getAvailability(), $num, $tf->getComment());
			}
		}
		$begin    = $first_day;
		$end      = $last_day->add(\DateInterval::createFromDateString('1 day'));
		$interval = \DateInterval::createFromDateString('1 day');
		$days     = new \DatePeriod($begin, $interval, $end);
		$skipped = array();
		foreach ( $days as $day ) {
			$d = $day->format('Y-m-d');
			$set = false;
			foreach ($rawavail as $a) {
				if(isset($a[$d])) {
					$set = true;
					break;
				}
			}
			if($set){
				if(count($skipped) > 0){
					if(count($skipped) < 4){
						array_merge($dates, $skipped);
					}else{
						$dates[] = $skipped[0];
						$dates[] = "…";
						$dates[] = $skipped[count($skipped)-1];
					}
					$skipped = array();
				}
				$dates[] = $d;
			} else {
				$skipped[] = $d;
			}
		}
		$mlen = array();
		foreach($dates as $date){
			if(!isset($mlen[substr($date, 0, 7)])) $mlen[substr($date, 0, 7)] = array(0, $date);
			$mlen[substr($date, 0, 7)][0]++;
		}
		
		return array(
					'friends' => $friends,
					'avail' => $avail,
					'dates' => $dates,
					'mlen' => $mlen,
				);
	}
    
    public function profileAction($id, $public)
    {
		$user = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($id);
			
		if (!$user) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if ($user->getPublickey() !== $public) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		$table = $this->drawTable(array(), $user);
		
        return $this->render('RamiFerientabelleBundle:Default:profile.html.twig', 
				array(
					'user' => $user,
					'table' => $table,
					
				));
    }
    
    public function timeframeDeleteAction($id, $public, $private, $tf)
    {
		$user = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($id);
			
		if (!$user) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if ($user->getPrivatekey() !== $private || $user->getPublickey() !== $public) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		$tfo = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:Timeframe')
			->find($tf);
			
		if (!$tfo) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		if ($tfo->getUser()->getId() !== $user->getId()) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		$em = $this->getDoctrine()->getManager();
		$em->remove($tfo);
		$em->flush();
		return $this->redirect($this->generateUrl('rami_ferientabelle_account', array('id' => $id, 'private' => $private, 'public' => $public)).'#frames');
    }
    
    public function addFriendDirectAction($id, $public)
    {
		$user = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($id);
			
		if (!$user) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if ($user->getPublickey() !== $public) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if (!$this->getUser()) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		$this->getUser()->addFriend($user);
		$em = $this->getDoctrine()->getManager();
		$em->flush();
		
			return $this->redirect(
				$this->generateUrl('rami_ferientabelle_account', 
					array(
						'id' => $this->getUser()->getId(), 
						'private' => $this->getUser()->getPrivatekey(), 
						'public' => $this->getUser()->getPublickey()
					)).'#friends');
		return $this->redirect($this->generateUrl('rami_ferientabelle_account', array('id' => $this->getUser()->getId(), 'private' => $this->getUser()->getPrivatekey(), 'public' => $this->getUser()->getPublickey())).'#friends');
    }
    
    public function addFriendAction($id, $public, $private)
    {
		$user = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($id);
			
		if (!$user) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if ($user->getPrivatekey() !== $private || $user->getPublickey() !== $public) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		$addfriend = $this->createForm(new FriendType(), null);
		
		$errmsg = false;
		
		$ntf = new Timeframe();
		$ntf->setUser($user);
		$nttf = $this->createForm(new TimeframeType(), $ntf);
		
		if ($this->getRequest()->isMethod('POST')) {
			$addfriend->bind($this->getRequest());
		
			if ($addfriend->isValid() ) {
				$url = $addfriend->getData()['publicurl'];
				if(preg_match('#/([0-9]+)/([0-9a-z]+)$#', $url, $sub) === 1){
					$f = $this->getDoctrine()
						->getRepository('RamiFerientabelleBundle:User')
						->find($sub[1]);
					if(!$f){
						$errmsg = 'Ungültige Adresse';
					} elseif($f->getPublickey() != $sub[2]) {
						$errmsg = 'Ungültige Adresse';
					} else {
						try {
							$user->addFriend($f);
							$em = $this->getDoctrine()->getManager();
							$em->flush();
						} catch(\Doctrine\DBAL\DBALException $e){
							
						}
					}
				}else{
					$errmsg = 'Ungültige Adresse';
				}
			}
		}
		return $this->accountAction($id, $public, $private, null, $errmsg);
    }
    
    public function deleteFriendAction($id, $public, $private, $friend)
    {
		$user = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($id);
			
		if (!$user) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		if ($user->getPrivatekey() !== $private || $user->getPublickey() !== $public) {
			throw $this->createNotFoundException(
				'Not found'
			);
		}
		
		$f = $this->getDoctrine()
			->getRepository('RamiFerientabelleBundle:User')
			->find($friend);
			
		$user->removeFriend($f);
		$em = $this->getDoctrine()->getManager();
		$em->flush();
		
		return $this->accountAction($id, $public, $private, null, null);
    }
    
    public function startAction()
    {
		$user = new User();
		$user->setName($this->getRequest()->get('name'));
		$keys = $this->genKeyPair();
		$user->setPrivatekey($keys[0]);
		$user->setPublickey($keys[1]);
		$em = $this->getDoctrine()->getManager();
		$em->persist($user);
		
		if($this->getRequest()->get('directlyadd')){
			$url = $this->getRequest()->get('directlyadd');
			if(preg_match('#/([0-9]+)/([0-9a-z]+)$#', $url, $sub) === 1){
				$f = $this->getDoctrine()
					->getRepository('RamiFerientabelleBundle:User')
					->find($sub[1]);
				if(!$f){
					die('Ungültige Adresse');
				} elseif($f->getPublickey() != $sub[2]) {
					die('Ungültige Adresse');
				} else {
					try {
						$user->addFriend($f);
						$em = $this->getDoctrine()->getManager();
					} catch(\Doctrine\DBAL\DBALException $e){
						
					}
				}
			}else{
				die('Ungültige Adresse');
			}
		}
		
		$em->flush();
		return $this->redirect($this->generateUrl('rami_ferientabelle_account', array('id' => $user->getId(), 'private' => $keys[0], 'public' => $keys[1])));
    }
    
    private function genKeyPair(){
		$hash = sha1(mt_rand().var_export($_SERVER, 1));
		return array( substr($hash, 0, 20), substr($hash, 20) );
	}
}
