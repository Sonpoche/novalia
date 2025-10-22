<?php
/**
 * Template du message de confirmation après envoi du devis
 *
 * @package NovaliaDevis
 * @subpackage Public/Views
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="nd-result-container">
    
    <!-- Icône de succès -->
    <div class="nd-success-animation">
        <div class="nd-success-checkmark">
            <div class="nd-check-icon">
                <span class="nd-icon-line nd-line-tip"></span>
                <span class="nd-icon-line nd-line-long"></span>
                <div class="nd-icon-circle"></div>
                <div class="nd-icon-fix"></div>
            </div>
        </div>
    </div>
    
    <!-- Message principal -->
    <h2 class="nd-result-title">
        <?php _e('Devis envoyé avec succès !', 'novalia-devis'); ?>
    </h2>
    
    <p class="nd-result-message">
        <?php _e('Votre devis a été généré et envoyé à votre adresse email.', 'novalia-devis'); ?>
    </p>
    
    <!-- Informations supplémentaires -->
    <div class="nd-result-info-box">
        <div class="nd-info-item">
            <span class="nd-info-icon">📧</span>
            <div class="nd-info-content">
                <strong><?php _e('Email envoyé', 'novalia-devis'); ?></strong>
                <p><?php _e('Vérifiez votre boîte de réception dans quelques instants.', 'novalia-devis'); ?></p>
            </div>
        </div>
        
        <div class="nd-info-item">
            <span class="nd-info-icon">📎</span>
            <div class="nd-info-content">
                <strong><?php _e('PDF joint', 'novalia-devis'); ?></strong>
                <p><?php _e('Votre devis détaillé est en pièce jointe au format PDF.', 'novalia-devis'); ?></p>
            </div>
        </div>
        
        <div class="nd-info-item">
            <span class="nd-info-icon">⏰</span>
            <div class="nd-info-content">
                <strong><?php _e('Validité 30 jours', 'novalia-devis'); ?></strong>
                <p><?php _e('Ce devis est gratuit et sans engagement.', 'novalia-devis'); ?></p>
            </div>
        </div>
    </div>
    
    <!-- Que faire ensuite -->
    <div class="nd-next-steps">
        <h3><?php _e('Et maintenant ?', 'novalia-devis'); ?></h3>
        
        <div class="nd-steps-grid">
            <div class="nd-step-card">
                <span class="nd-step-number">1</span>
                <h4><?php _e('Consultez votre email', 'novalia-devis'); ?></h4>
                <p><?php _e('Vérifiez votre boîte de réception et vos courriers indésirables.', 'novalia-devis'); ?></p>
            </div>
            
            <div class="nd-step-card">
                <span class="nd-step-number">2</span>
                <h4><?php _e('Lisez le devis PDF', 'novalia-devis'); ?></h4>
                <p><?php _e('Ouvrez la pièce jointe pour voir tous les détails de votre déménagement.', 'novalia-devis'); ?></p>
            </div>
            
            <div class="nd-step-card">
                <span class="nd-step-number">3</span>
                <h4><?php _e('Contactez-nous', 'novalia-devis'); ?></h4>
                <p><?php _e('Pour toute question ou pour confirmer votre réservation.', 'novalia-devis'); ?></p>
            </div>
        </div>
    </div>
    
    <?php
    // Récupération des informations de l'entreprise
    $company = get_option('nd_company');
    ?>
    
    <!-- Informations de contact -->
    <div class="nd-contact-box">
        <h3><?php _e('Besoin d\'aide ?', 'novalia-devis'); ?></h3>
        <p><?php _e('Notre équipe est à votre disposition pour répondre à toutes vos questions.', 'novalia-devis'); ?></p>
        
        <div class="nd-contact-actions">
            <?php if (!empty($company['phone'])): ?>
                <a href="tel:<?php echo esc_attr($company['phone']); ?>" class="nd-btn nd-btn-primary">
                    <span class="nd-btn-icon">📞</span>
                    <?php echo esc_html($company['phone']); ?>
                </a>
            <?php endif; ?>
            
            <?php if (!empty($company['email'])): ?>
                <a href="mailto:<?php echo esc_attr($company['email']); ?>" class="nd-btn nd-btn-secondary">
                    <span class="nd-btn-icon">📧</span>
                    <?php echo esc_html($company['email']); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Bouton nouveau devis -->
    <div class="nd-result-actions">
        <button type="button" class="nd-btn nd-btn-outline" id="nd-new-quote-btn">
            <span class="nd-btn-icon">🔄</span>
            <?php _e('Faire un nouveau devis', 'novalia-devis'); ?>
        </button>
    </div>
    
    <!-- Note supplémentaire -->
    <div class="nd-result-note">
        <p>
            <strong><?php _e('Vous n\'avez pas reçu l\'email ?', 'novalia-devis'); ?></strong><br>
            <?php _e('Pensez à vérifier vos courriers indésirables (spam). Si vous ne trouvez toujours pas l\'email, contactez-nous directement.', 'novalia-devis'); ?>
        </p>
    </div>
    
</div>

<style>
/* Styles spécifiques pour le résultat */
.nd-result-container {
    text-align: center;
    padding: 40px 20px;
    animation: fadeInUp 0.6s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Animation de succès */
.nd-success-animation {
    margin: 0 auto 30px;
}

.nd-success-checkmark {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    position: relative;
}

.nd-check-icon {
    width: 80px;
    height: 80px;
    position: relative;
    border-radius: 50%;
    box-sizing: content-box;
    border: 4px solid #48bb78;
    animation: checkmarkPop 0.6s ease;
}

@keyframes checkmarkPop {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

.nd-icon-line {
    height: 5px;
    background-color: #48bb78;
    display: block;
    border-radius: 2px;
    position: absolute;
    z-index: 10;
}

.nd-line-tip {
    top: 46px;
    left: 14px;
    width: 25px;
    transform: rotate(45deg);
    animation: checkmarkTip 0.4s 0.3s ease forwards;
}

.nd-line-long {
    top: 38px;
    right: 8px;
    width: 47px;
    transform: rotate(-45deg);
    animation: checkmarkLong 0.4s 0.5s ease forwards;
}

@keyframes checkmarkTip {
    0% {
        width: 0;
        left: 1px;
        top: 19px;
    }
    100% {
        width: 25px;
        left: 14px;
        top: 46px;
    }
}

@keyframes checkmarkLong {
    0% {
        width: 0;
        right: 46px;
        top: 54px;
    }
    100% {
        width: 47px;
        right: 8px;
        top: 38px;
    }
}

.nd-icon-circle {
    top: -4px;
    left: -4px;
    z-index: 10;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    position: absolute;
    box-sizing: content-box;
    border: 4px solid rgba(72, 187, 120, .3);
}

.nd-result-title {
    font-size: 32px;
    color: #2d3748;
    margin: 20px 0 15px;
    font-weight: 700;
}

.nd-result-message {
    font-size: 18px;
    color: #718096;
    margin-bottom: 40px;
}

/* Boîtes d'information */
.nd-result-info-box {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 40px 0;
    text-align: left;
}

.nd-info-item {
    display: flex;
    gap: 15px;
    padding: 20px;
    background: #f7fafc;
    border-radius: 12px;
    border: 2px solid #e2e8f0;
}

.nd-info-icon {
    font-size: 32px;
    flex-shrink: 0;
}

.nd-info-content h4,
.nd-info-content strong {
    margin: 0 0 8px 0;
    font-size: 16px;
    color: #2d3748;
}

.nd-info-content p {
    margin: 0;
    font-size: 14px;
    color: #718096;
    line-height: 1.5;
}

/* Prochaines étapes */
.nd-next-steps {
    margin: 50px 0;
    padding: 30px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px;
    color: white;
}

.nd-next-steps h3 {
    margin: 0 0 30px 0;
    font-size: 24px;
}

.nd-steps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.nd-step-card {
    background: rgba(255, 255, 255, 0.1);
    padding: 25px;
    border-radius: 12px;
    backdrop-filter: blur(10px);
}

.nd-step-number {
    display: inline-block;
    width: 40px;
    height: 40px;
    line-height: 40px;
    background: white;
    color: #667eea;
    border-radius: 50%;
    font-weight: 700;
    font-size: 20px;
    margin-bottom: 15px;
}

.nd-step-card h4 {
    margin: 0 0 10px 0;
    font-size: 16px;
}

.nd-step-card p {
    margin: 0;
    font-size: 14px;
    opacity: 0.9;
}

/* Contact box */
.nd-contact-box {
    margin: 40px 0;
    padding: 30px;
    background: #f7fafc;
    border-radius: 12px;
}

.nd-contact-box h3 {
    margin: 0 0 10px 0;
    font-size: 22px;
    color: #2d3748;
}

.nd-contact-box p {
    margin: 0 0 20px 0;
    color: #718096;
}

.nd-contact-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

/* Actions */
.nd-result-actions {
    margin: 40px 0;
}

.nd-btn-outline {
    background: white;
    color: #667eea;
    border: 2px solid #667eea;
}

.nd-btn-outline:hover {
    background: #667eea;
    color: white;
}

/* Note */
.nd-result-note {
    margin-top: 40px;
    padding: 20px;
    background: #fff3cd;
    border-left: 4px solid #ffc107;
    border-radius: 8px;
    text-align: left;
}

.nd-result-note p {
    margin: 0;
    font-size: 14px;
    color: #856404;
    line-height: 1.6;
}

/* Responsive */
@media (max-width: 768px) {
    .nd-result-title {
        font-size: 24px;
    }
    
    .nd-result-message {
        font-size: 16px;
    }
    
    .nd-result-info-box,
    .nd-steps-grid {
        grid-template-columns: 1fr;
    }
    
    .nd-contact-actions {
        flex-direction: column;
    }
    
    .nd-contact-actions .nd-btn {
        width: 100%;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Bouton nouveau devis
    $('#nd-new-quote-btn').on('click', function() {
        location.reload();
    });
});
</script>