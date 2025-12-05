<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Assessment;
use App\Form\AssessmentType;
use App\Repository\AssessmentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(AssessmentRepository $assessmentRepository): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        // Récupère les évaluations de la classe de l'utilisateur connecté
        $assessments = $assessmentRepository->findBy(
            [
                'schoolClass' => $user->getSchoolClass(),
            ],
            [
                'date' => 'ASC',
            ]
        );

        foreach ($assessments as $assessment) {
            $scores = $assessment->getScores();
            $total = $assessment->getSchoolClass()
                ->getChildren()
                ->count();
            $present = $scores->filter(fn ($s) => $s->getScore() !== null)
                ->count();
            $absent = $scores->filter(fn ($s) => $s->isAbsent() === true)
                ->count();

            $scoresValues = $scores
                ->filter(fn ($s) => $s->getScore() !== null)
                ->map(fn ($s) => $s->getScore())
                ->toArray();

            $min = $scoresValues ? min($scoresValues) : 0;
            $max = $scoresValues ? max($scoresValues) : 0;
            $avg = $scoresValues ? array_sum($scoresValues) / count($scoresValues) : 0;

            $less40 = count(array_filter($scoresValues, fn ($s) => $s < 0.4 * $assessment->getMaxScore()));
            $between41_75 = count(
                array_filter(
                    $scoresValues,
                    fn ($s) => $s >= 0.41 * $assessment->getMaxScore() && $s <= 0.75 * $assessment->getMaxScore()
                )
            );
            $more75 = count(array_filter($scoresValues, fn ($s) => $s > 0.75 * $assessment->getMaxScore()));
            if ($total === 0) {
                $total = 1;
            }
            $assessment->stats = [
                'total' => $total,
                'present' => $present,
                'absent' => $absent,
                'min' => $min,
                'max' => $max,
                'avg' => $avg,
                'less40' => $less40,
                'between41_75' => $between41_75,
                'more75' => $more75,
            ];
        }

        // Regroupe par thème
        $groupedByTheme = [];
        foreach ($assessments as $assessment) {
            $themeName = $assessment->getTheme()
                ->getName();
            if (! isset($groupedByTheme[$themeName])) {
                $groupedByTheme[$themeName] = [];
            }
            $groupedByTheme[$themeName][] = $assessment;
        }

        ksort($groupedByTheme, SORT_NATURAL | SORT_FLAG_CASE);

        foreach ($groupedByTheme as $assessments) {
            foreach ($assessments as $assessment) {
                $scores = $assessment->getScores()
                    ->toArray(); // récupérer dans une variable
                usort($scores, function ($a, $b) {
                    return strcmp($a->getChild()->getLastName(), $b->getChild()->getLastName())
                        ?: strcmp($a->getChild()->getFirstName(), $b->getChild()->getFirstName());
                });
                $assessment->setScores($scores); // si tu as un setter, mettre à jour l'objet
            }
        }

        return $this->render('front/index.html.twig', [
            'user' => $user,
            'groupedByTheme' => $groupedByTheme,
        ]);
    }

    #[Route('/assessment/new', name: 'assessment_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if (! $user) {
            return $this->redirectToRoute('app_login');
        }

        $assessment = new Assessment();
        $assessment->setSchoolClass($user->getSchoolClass());

        $form = $this->createForm(AssessmentType::class, $assessment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($assessment);
            $em->flush();

            $this->addFlash('success', 'Évaluation créée avec succès !');
            return $this->redirectToRoute('score_edit', [
                'assessment' => $assessment->getId(),
            ]);
        }

        return $this->render('/front/assessment/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }
}
