<?php

declare(strict_types=1);

namespace App\Core\Application\Api\Responses;

/**
 * Codes HTTP exposés dans l'enveloppe {@see BaseResponse} pour l'API.
 */
enum HttpResponseCode: int
{
    case Ok = 200;
    case Created = 201;
    case Accepted = 202;
    case NoContent = 204;
    case BadRequest = 400;
    case Unauthorized = 401;
    case PaymentRequired = 402;
    case Forbidden = 403;
    case NotFound = 404;
    case MethodNotAllowed = 405;
    case NotAcceptable = 406;
    case Conflict = 409;
    case Gone = 410;
    case PayloadTooLarge = 413;
    case UnsupportedMediaType = 415;
    case UnprocessableEntity = 422;
    case TooManyRequests = 429;
    case InternalServerError = 500;
    case NotImplemented = 501;
    case BadGateway = 502;
    case ServiceUnavailable = 503;
    case GatewayTimeout = 504;

    public function defaultMessage(): string
    {
        return match ($this) {
            self::Ok => 'Opération réussie.',
            self::Created => 'Ressource créée.',
            self::Accepted => 'Requête acceptée.',
            self::NoContent => 'Aucun contenu.',
            self::BadRequest => 'Requête invalide.',
            self::Unauthorized => 'Non authentifié.',
            self::PaymentRequired => 'Paiement requis.',
            self::Forbidden => 'Accès refusé.',
            self::NotFound => 'Ressource introuvable.',
            self::MethodNotAllowed => 'Méthode non autorisée.',
            self::NotAcceptable => 'Réponse non acceptable.',
            self::Conflict => 'Conflit.',
            self::Gone => 'Ressource indisponible.',
            self::PayloadTooLarge => 'Charge utile trop volumineuse.',
            self::UnsupportedMediaType => 'Type de média non supporté.',
            self::UnprocessableEntity => 'Données de validation invalides.',
            self::TooManyRequests => 'Trop de requêtes. Réessayez plus tard.',
            self::InternalServerError => 'Erreur interne du serveur.',
            self::NotImplemented => 'Non implémenté.',
            self::BadGateway => 'Passerelle invalide.',
            self::ServiceUnavailable => 'Service indisponible.',
            self::GatewayTimeout => 'Délai de la passerelle dépassé.',
        };
    }
}
