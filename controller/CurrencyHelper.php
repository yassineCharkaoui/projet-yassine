<?php
/**
 * Helper pour la conversion de devises
 * Taux de change au 2 septembre 2026
 */

class CurrencyHelper
{
    // Taux de change par rapport à l'EUR
    private const EXCHANGE_RATES = [
        'EUR' => 1.0,        // Euro (devise de base)
        'USD' => 1.09,       // Dollar américain
        'TND' => 3.38        // Dinar tunisien
    ];
    
    // Symboles des devises
    private const CURRENCY_SYMBOLS = [
        'EUR' => '€',
        'USD' => '$',
        'TND' => 'د.ت'
    ];
    
    /**
     * Convertir un montant d'EUR vers une autre devise
     * 
     * @param float $amount Montant en EUR
     * @param string $toCurrency Code de la devise cible (EUR, USD, TND)
     * @return float Montant converti
     */
    public static function convert(float $amount, string $toCurrency = 'EUR'): float
    {
        if (!isset(self::EXCHANGE_RATES[$toCurrency])) {
            return $amount; // Devise non supportée, retourner le montant original
        }
        
        return $amount * self::EXCHANGE_RATES[$toCurrency];
    }
    
    /**
     * Formater un montant avec le symbole de la devise
     * 
     * @param float $amount Montant
     * @param string $currency Code de la devise (EUR, USD, TND)
     * @param int $decimals Nombre de décimales
     * @return string Montant formaté avec symbole
     */
    public static function format(float $amount, string $currency = 'EUR', int $decimals = 2): string
    {
        $symbol = self::CURRENCY_SYMBOLS[$currency] ?? $currency;
        $formatted = number_format($amount, $decimals, '.', ' ');
        
        // Pour TND et EUR, mettre le symbole à la fin
        if ($currency === 'TND' || $currency === 'EUR') {
            return $formatted . ' ' . $symbol;
        }
        
        // Pour USD, mettre le symbole au début
        return $symbol . ' ' . $formatted;
    }
    
    /**
     * Convertir et formater en une seule étape
     * 
     * @param float $amount Montant en EUR
     * @param string $currency Code de la devise cible
     * @param int $decimals Nombre de décimales
     * @return string Montant converti et formaté
     */
    public static function convertAndFormat(float $amount, string $currency = 'EUR', int $decimals = 2): string
    {
        $converted = self::convert($amount, $currency);
        return self::format($converted, $currency, $decimals);
    }
    
    /**
     * Obtenir toutes les devises supportées
     * 
     * @return array Tableau des codes de devises avec leurs noms
     */
    public static function getSupportedCurrencies(): array
    {
        return [
            'EUR' => 'Euro (€)',
            'USD' => 'Dollar américain ($)',
            'TND' => 'Dinar tunisien (د.ت)'
        ];
    }
    
    /**
     * Obtenir le symbole d'une devise
     * 
     * @param string $currency Code de la devise
     * @return string Symbole de la devise
     */
    public static function getSymbol(string $currency): string
    {
        return self::CURRENCY_SYMBOLS[$currency] ?? $currency;
    }
    
    /**
     * Obtenir le taux de change
     * 
     * @param string $currency Code de la devise
     * @return float Taux de change par rapport à l'EUR
     */
    public static function getRate(string $currency): float
    {
        return self::EXCHANGE_RATES[$currency] ?? 1.0;
    }
}
?>
