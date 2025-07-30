<?php

namespace App\Controller;

use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HistoriqueController extends AbstractController
{
    #[Route('/historique', name: 'app_historique')]
    public function index(Connection $connection): Response
    {
        $auditTables = [
            'alimentation_audit' => 'Alimentation',
            'categorie_audit' => 'Catégorie',
            'depense_audit' => 'Dépense',
            'user_audit' => 'Utilisateur',
        ];

        $unionQuery = [];
        foreach ($auditTables as $table => $entityName) {
            $unionQuery[] = sprintf(
                "(SELECT id, '%s' as entity_type, object_id, type, blame_user as user_id, created_at, diffs as body FROM %s)",
                $entityName,
                $table
            );
        }

        $sql = implode(' UNION ALL ', $unionQuery) . ' ORDER BY created_at DESC LIMIT 200';

        $auditData = $connection->fetchAllAssociative($sql);

        foreach ($auditData as &$entry) {
            $entry['formatted_body'] = $this->formatAuditBody($entry['body'], $entry['type'], $entry['entity_type']);
            // Normalisation des types d'action
            $entry['type'] = $this->normalizeActionType($entry['type']);
        }

        return $this->render('historique/index.html.twig', [
            'auditData' => $auditData,
        ]);
    }

    private function normalizeActionType(string $actionType): string
    {
        return match (strtolower($actionType)) {
            'insert', 'create' => 'Création',
            'update', 'modify' => 'Modification',
            'delete', 'remove' => 'Suppression',
            default => $actionType,
        };
    }

    private function formatAuditBody(?string $jsonBody, string $actionType, string $entityType): string
    {
        $actionType = strtolower($actionType);
        
        if (empty($jsonBody)) {
            return match ($actionType) {
                'insert', 'create' => "Nouvel enregistrement créé",
                'delete', 'remove' => "Enregistrement supprimé",
                default => "Action effectuée",
            };
        }

        $diffs = json_decode($jsonBody, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return "Détails des modifications non disponibles";
        }

        $messages = [];
        $simpleFields = ['montant', 'dateAction', 'source', 'nom', 'description', 'email']; // Adaptez cette liste
        
        if (in_array($actionType, ['insert', 'create'])) {
            $messages[] = "Nouvel enregistrement créé avec les valeurs :";
            foreach ($simpleFields as $field) {
                if (isset($diffs[$field]['new'])) {
                    $messages[] = sprintf("- %s: %s", $field, $this->formatSimpleValue($diffs[$field]['new']));
                }
            }
        } elseif (in_array($actionType, ['update', 'modify'])) {
            $messages[] = "Modifications apportées :";
            foreach ($diffs as $field => $change) {
                if (str_starts_with($field, '@')) continue;
                if (in_array($field, $simpleFields)) {
                    $oldValue = $change['old'] ?? null;
                    $newValue = $change['new'] ?? null;
                    $messages[] = sprintf("- %s: %s → %s", 
                        $field, 
                        $this->formatSimpleValue($oldValue), 
                        $this->formatSimpleValue($newValue));
                }
            }
        } elseif (in_array($actionType, ['delete', 'remove'])) {
            $messages[] = "Enregistrement supprimé ";
            foreach ($simpleFields as $field) {
                if (isset($diffs[$field])) {
                    $messages[] = sprintf("- %s: %s", $field, $this->formatSimpleValue($diffs[$field]));
                }
            }
        }

        return empty($messages) ? "Aucun détail disponible" : implode("\n", $messages);
    }

    private function formatSimpleValue($value): string
    {
        if (is_null($value)) {
            return 'non défini';
        }
        if (is_bool($value)) {
            return $value ? 'oui' : 'non';
        }
        if (is_array($value)) {
            return isset($value['id']) ? 'ID '.$value['id'] : 'donnée composite';
        }
        if ($value instanceof \DateTimeInterface) {
            return $value->format('d/m/Y H:i');
        }
        return (string) $value;
    }
}