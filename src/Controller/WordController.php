<?php

namespace App\Controller;

use App\Entity\Letter;
use App\Repository\GameRepository;
use App\Repository\LetterRepository;
use App\Repository\WordRepository;
use App\Service\LetterManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
/**
 * @Route("/word", name="word_")
 */
class WordController extends AbstractController
{
    /**
     * @Route("/check", name="check")
     * @param Request $request
     * @param GameRepository $gameRepository
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function check(Request $request, GameRepository $gameRepository, EntityManagerInterface $entityManager)
    {
        $letterChecked = $request->query->get('letter');
        $game = $gameRepository->findOneBy([]);
        $wordLetters = str_split($game->getWord()->getWord());
        $win  = in_array($letterChecked, $wordLetters);

        $letter = new Letter();
        $letter->setLetter($letterChecked);
        $letter->setIsInTheWord($win);
        $entityManager->persist($letter);
        $entityManager->flush();

        if ($win) {
            $this->addFlash('SUCCESS', 'Cette lettre est dans le mot !');
        } else {
            $game->setStep($game->getStep() +1);
            $entityManager->persist($game);
            $entityManager->flush();
            $this->addFlash('FAIL', 'Pas de chance...');
        }
        $entityManager->flush();
        return $this->redirectToRoute('word_word');
    }

    /**
     * @Route("/replay", name="replay")
     * @param GameRepository $gameRepository
     * @param WordRepository $wordRepository
     * @param LetterRepository $letterRepository
     * @param EntityManagerInterface $entityManager
     * @return RedirectResponse
     */
    public function replay(GameRepository $gameRepository, WordRepository $wordRepository, LetterRepository $letterRepository, EntityManagerInterface $entityManager)
    {
        $game = $gameRepository->findOneBy([]);
        $game->setStep(0);
        $game->setWord($wordRepository->findOneById(rand(1,5)));
        $letterRepository->createQueryBuilder('l')->where('l.id >= 1')->delete()->getQuery()->execute();
        $entityManager->persist($game);
        $entityManager->flush();
        return $this->redirectToRoute('word_word');
    }

    /**
     * @Route("/word", name="word")
     * @param GameRepository $gameRepository
     * @param LetterRepository $letterRepository
     * @param LetterManager $letterManager
     * @return Response
     */
    public function index(GameRepository $gameRepository, LetterRepository $letterRepository, LetterManager $letterManager)
    {
        $wrongLetters = $letterRepository->findBy(['isInTheWord' => false]);
        $rightLetters = $letterRepository->findBy(['isInTheWord' => true]);
        $game = $gameRepository->findOneBy([]);
        $word = $game->getWord()->getWord();

        $responses = $letterManager->getLetters($word, $rightLetters);

        $checkWin = $letterManager->checkWin($word, $responses);

        return $this->render('word/index.html.twig', [
            'game'         => $game,
            'wrongLetters' => $wrongLetters,
            'responses'    => $responses,
            'checkWin'     => $checkWin
        ]);
    }
}
