<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220808230943 extends AbstractMigration
{

    protected static $authoritiesOptions = [
        [ 'id' => 1, 'label' => 'SECO', 'supportsProvinces' => false ],
        [ 'id' => 2, 'label' => 'ARE', 'supportsProvinces' => false ],
        [ 'id' => 3, 'label' => 'BAFU', 'supportsProvinces' => false ],
        [ 'id' => 4, 'label' => 'BLW', 'supportsProvinces' => false ],
        [ 'id' => 5, 'label' => 'BFE', 'supportsProvinces' => false ],
        [ 'id' => 6, 'label' => 'Kantone', 'supportsProvinces' => true ],
        [ 'id' => 7, 'label' => 'BAG', 'supportsProvinces' => false ],
        [ 'id' => 8, 'label' => 'BWO', 'supportsProvinces' => false ],
        [ 'id' => 9, 'label' => 'ASTRA', 'supportsProvinces' => false ],
        [ 'id' => 10, 'label' => 'BASPO', 'supportsProvinces' => false ],
        [ 'id' => 11, 'label' => 'Privat', 'supportsProvinces' => false ],
        [ 'id' => 12, 'label' => 'UVEK', 'supportsProvinces' => false ],
        [ 'id' => 13, 'label' => 'SBFI', 'supportsProvinces' => false ],
        [ 'id' => 14, 'label' => 'Bund', 'supportsProvinces' => false ],
        [ 'id' => 15, 'label' => 'Kanton Wallis', 'supportsProvinces' => false ],
    ];

    protected static $beneficiariesOptions = [
        [ 'id' => 1, 'label' => 'Kanton' ],
        [ 'id' => 2, 'label' => 'Region' ],
        [ 'id' => 3, 'label' => 'Gemeinde' ],
        [ 'id' => 4, 'label' => 'Einzelbetrieb' ],
        [ 'id' => 5, 'label' => 'Überbetriebliches Netzwerk' ],
        [ 'id' => 6, 'label' => 'Landwirtschaftsbetrieb' ],
        [ 'id' => 7, 'label' => 'Verein und Verband' ],
        [ 'id' => 8, 'label' => 'Privatperson' ],
        [ 'id' => 9, 'label' => 'Forschung und Bildung' ],
        [ 'id' => 19, 'label' => 'Weitere' ],
    ];

    protected static $topicsOptions = [
        [ 'id' => 1, 'label' => 'Wirtschaft', 'icon' => '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.50989 1.00001C3.06217 1.00001 3.50989 1.44772 3.50989 2.00001V13H14.5099C15.0622 13 15.5099 13.4477 15.5099 14C15.5099 14.5523 15.0622 15 14.5099 15H2.50989C1.9576 15 1.50989 14.5523 1.50989 14V2.00001C1.50989 1.44772 1.9576 1.00001 2.50989 1.00001Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M14.9571 1.10558C15.4511 1.35257 15.6513 1.95324 15.4043 2.44722L11.4043 10.4472C11.276 10.7038 11.0438 10.8929 10.7666 10.9665C10.4893 11.0401 10.1938 10.9912 9.95519 10.8321L7.78724 9.38676L6.34194 11.5547C6.03559 12.0142 5.41472 12.1384 4.95519 11.8321C4.49566 11.5257 4.37148 10.9048 4.67784 10.4453L6.67784 7.4453C6.98419 6.98578 7.60506 6.8616 8.06459 7.16795L10.1221 8.5396L13.6155 1.55279C13.8624 1.05881 14.4631 0.858589 14.9571 1.10558Z" fill="black"/></svg>' ],
        [ 'id' => 2, 'label' => 'Industrie', 'icon' => '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M8.50994 13.6268C11.6175 13.6268 14.1367 11.1076 14.1367 7.99999C14.1367 4.89241 11.6175 2.37321 8.50994 2.37321C5.40235 2.37321 2.88316 4.89241 2.88316 7.99999C2.88316 11.1076 5.40235 13.6268 8.50994 13.6268ZM8.50992 11.376C10.3745 11.376 11.886 9.86446 11.886 7.99991C11.886 6.13536 10.3745 4.62384 8.50992 4.62384C6.64537 4.62384 5.13385 6.13536 5.13385 7.99991C5.13385 9.86446 6.64537 11.376 8.50992 11.376Z" fill="black"/><path d="M7.48019 1.29282C7.48019 1.01667 7.70405 0.792816 7.98019 0.792816H9.03948C9.31562 0.792816 9.53948 1.01667 9.53948 1.29282V3.38174C9.53948 3.65789 9.31562 3.88174 9.03948 3.88174H7.98019C7.70405 3.88174 7.48019 3.65788 7.48019 3.38174V1.29282Z" fill="black"/><path d="M7.48019 12.6189C7.48019 12.3428 7.70405 12.1189 7.98019 12.1189H9.03948C9.31562 12.1189 9.53948 12.3428 9.53948 12.6189V14.7078C9.53948 14.984 9.31562 15.2078 9.03948 15.2078H7.98019C7.70405 15.2078 7.48019 14.984 7.48019 14.7078V12.6189Z" fill="black"/><path d="M15.2173 6.97073C15.4935 6.97073 15.7173 7.19459 15.7173 7.47073V8.53002C15.7173 8.80616 15.4935 9.03002 15.2173 9.03002H13.1284C12.8523 9.03002 12.6284 8.80616 12.6284 8.53002V7.47073C12.6284 7.19459 12.8523 6.97073 13.1284 6.97073H15.2173Z" fill="black"/><path d="M3.89123 6.97073C4.16738 6.97073 4.39123 7.19459 4.39123 7.47073V8.53002C4.39123 8.80616 4.16738 9.03002 3.89123 9.03002H1.80231C1.52616 9.03002 1.30231 8.80616 1.30231 8.53002L1.30231 7.47073C1.30231 7.19459 1.52616 6.97073 1.80231 6.97073H3.89123Z" fill="black"/><path d="M12.5247 2.52941C12.7199 2.33415 13.0365 2.33415 13.2318 2.52941L13.9808 3.27844C14.1761 3.4737 14.1761 3.79028 13.9808 3.98554L12.5037 5.46264C12.3084 5.6579 11.9919 5.6579 11.7966 5.46264L11.0476 4.71361C10.8523 4.51835 10.8523 4.20176 11.0476 4.0065L12.5247 2.52941Z" fill="black"/><path d="M4.51585 10.5379C4.71111 10.3427 5.02769 10.3427 5.22295 10.5379L5.97198 11.287C6.16724 11.4822 6.16724 11.7988 5.97198 11.9941L4.49489 13.4712C4.29962 13.6664 3.98304 13.6664 3.78778 13.4712L3.03875 12.7222C2.84349 12.5269 2.84349 12.2103 3.03875 12.015L4.51585 10.5379Z" fill="black"/><path d="M13.9805 12.0151C14.1758 12.2103 14.1758 12.5269 13.9805 12.7222L13.2315 13.4712C13.0362 13.6665 12.7197 13.6665 12.5244 13.4712L11.0473 11.9941C10.852 11.7988 10.852 11.4823 11.0473 11.287L11.7963 10.538C11.9916 10.3427 12.3082 10.3427 12.5034 10.538L13.9805 12.0151Z" fill="black"/><path d="M5.972 4.00652C6.16726 4.20179 6.16726 4.51837 5.97199 4.71363L5.22297 5.46266C5.02771 5.65792 4.71112 5.65792 4.51586 5.46266L3.03877 3.98556C2.84351 3.7903 2.84351 3.47372 3.03877 3.27846L3.78779 2.52943C3.98306 2.33417 4.29964 2.33417 4.4949 2.52943L5.972 4.00652Z" fill="black"/><path d="M9.89179 7.99993C9.89179 8.76314 9.27309 9.38184 8.50988 9.38184C7.74667 9.38184 7.12797 8.76314 7.12797 7.99993C7.12797 7.23672 7.74667 6.61802 8.50988 6.61802C9.27309 6.61802 9.89179 7.23672 9.89179 7.99993Z" fill="black"/></svg>' ],
        [ 'id' => 3, 'label' => 'Tourismus', 'icon' => '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.67784 1.4453C5.8633 1.1671 6.17553 1 6.50989 1H10.5099C10.8442 1 11.1565 1.1671 11.3419 1.4453L13.0451 4H14.5099C15.0622 4 15.5099 4.44772 15.5099 5V14C15.5099 14.5523 15.0622 15 14.5099 15H2.50989C1.9576 15 1.50989 14.5523 1.50989 14V5C1.50989 4.44772 1.9576 4 2.50989 4H3.9747L5.67784 1.4453ZM7.04507 3L5.34194 5.5547C5.15647 5.8329 4.84424 6 4.50989 6H3.50989V13H13.5099V6H12.5099C12.1755 6 11.8633 5.8329 11.6778 5.5547L9.9747 3H7.04507Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.50989 8C7.9576 8 7.50989 8.44772 7.50989 9C7.50989 9.55228 7.9576 10 8.50989 10C9.06217 10 9.50989 9.55228 9.50989 9C9.50989 8.44772 9.06217 8 8.50989 8ZM11.5099 9C11.5099 10.6569 10.1667 12 8.50989 12C6.85303 12 5.50989 10.6569 5.50989 9C5.50989 7.34315 6.85303 6 8.50989 6C10.1667 6 11.5099 7.34315 11.5099 9Z" fill="black"/></svg>' ],
        [ 'id' => 4, 'label' => 'Raumentwicklung und Mobilität', 'icon' => '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M5.80278 1.29289C6.19331 0.902369 6.82647 0.902369 7.21699 1.29289L11.217 5.29289C11.4045 5.48043 11.5099 5.73478 11.5099 6V9C11.5099 9.37877 11.2959 9.72503 10.9571 9.89443L8.9571 10.8944C8.81825 10.9639 8.66513 11 8.50989 11H2.50989C1.9576 11 1.50989 10.5523 1.50989 10V6C1.50989 5.73478 1.61524 5.48043 1.80278 5.29289L5.80278 1.29289ZM3.50989 6.41421V9H8.27382L9.50989 8.38197V6.41421L6.50989 3.41421L3.50989 6.41421Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M13.7421 9.35933C14.0957 8.9355 14.726 8.87835 15.1501 9.23178C15.5743 9.58534 15.6317 10.2159 15.2781 10.6402L14.5099 10C15.2781 10.6402 15.2781 10.6402 15.2781 10.6402L15.2759 10.6428L15.2728 10.6465L15.2637 10.6572L15.2346 10.691C15.2102 10.7189 15.1762 10.7573 15.1328 10.8046C15.046 10.8993 14.9214 11.0303 14.7619 11.1855C14.4439 11.495 13.9818 11.9056 13.3991 12.317C12.2456 13.1312 10.5446 14 8.50989 14H5.50989C4.68265 14 4.08481 14.2056 3.7071 14.3944C3.51661 14.4897 3.37993 14.5818 3.29739 14.6438C3.25619 14.6747 3.22879 14.6978 3.21527 14.7096C3.2112 14.7132 3.20841 14.7157 3.20689 14.7171C2.81562 15.0976 2.18995 15.0943 1.80278 14.7071C1.41226 14.3166 1.41226 13.6834 1.80278 13.2929L2.49417 13.9843C1.80279 13.2929 1.80278 13.2929 1.80278 13.2929L1.80421 13.2915L1.80574 13.2899L1.8091 13.2866L1.81702 13.2789L1.83767 13.2592C1.85356 13.2443 1.87376 13.2259 1.89826 13.2045C1.94723 13.1616 2.01359 13.1066 2.09739 13.0438C2.26485 12.9182 2.50317 12.7603 2.81267 12.6056C3.43496 12.2944 4.33712 12 5.50989 12H8.50989C9.97516 12 11.2742 11.3688 12.2457 10.683C12.7254 10.3444 13.1072 10.005 13.3672 9.75202C13.4968 9.62596 13.5949 9.52259 13.6585 9.45318C13.6903 9.41852 13.7133 9.39244 13.7273 9.37637L13.7421 9.35933Z" fill="black"/></svg>' ],
        [ 'id' => 5, 'label' => 'Land- und Ernährungswirtschaft', 'icon' => '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.01129 1.94744C2.03925 1.41632 2.47805 1 3.00991 1H8.50991C8.88868 1 9.23495 1.214 9.40434 1.55279L11.1279 5H13.5099C13.9988 5 14.4159 5.35341 14.4963 5.8356L15.4963 11.8356C15.5871 12.3804 15.2191 12.8956 14.6743 12.9864C14.1295 13.0772 13.6143 12.7092 13.5235 12.1644L12.6628 7H11.0099L8.30991 10.6C7.97854 11.0418 7.35174 11.1314 6.90991 10.8C6.46808 10.4686 6.37854 9.84183 6.70991 9.4L9.33909 5.89443L7.89188 3H3.95867L3.50853 11.5526C3.4795 12.1041 3.00888 12.5276 2.45735 12.4986C1.90583 12.4696 1.48227 11.999 1.51129 11.4474L2.01129 1.94744Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M11.1699 5.12C11.6559 5.48451 11.7544 6.17399 11.3899 6.66L8.38991 10.66C8.0254 11.146 7.33592 11.2445 6.84991 10.88C6.3639 10.5155 6.2654 9.82601 6.62991 9.34L9.62991 5.34C9.99442 4.85399 10.6839 4.75549 11.1699 5.12Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M12.5099 11C11.9576 11 11.5099 11.4477 11.5099 12C11.5099 12.5523 11.9576 13 12.5099 13C13.0622 13 13.5099 12.5523 13.5099 12C13.5099 11.4477 13.0622 11 12.5099 11ZM15.5099 12C15.5099 13.6569 14.1668 15 12.5099 15C10.8531 15 9.50991 13.6569 9.50991 12C9.50991 10.3431 10.8531 9 12.5099 9C14.1668 9 15.5099 10.3431 15.5099 12Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M8.50991 11.5C8.50991 13.433 6.94291 15 5.00991 15C3.07692 15 1.50991 13.433 1.50991 11.5C1.50991 9.567 3.07692 8 5.00991 8C6.94291 8 8.50991 9.567 8.50991 11.5ZM5.00991 13C5.83834 13 6.50991 12.3284 6.50991 11.5C6.50991 10.6716 5.83834 10 5.00991 10C4.18149 10 3.50991 10.6716 3.50991 11.5C3.50991 12.3284 4.18149 13 5.00991 13Z" fill="black"/></svg>' ],
        [ 'id' => 6, 'label' => 'Umwelt und Landschaft', 'icon' => '<svg width="17" height="16" viewBox="0 0 17 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M4.24394 2.0576C4.17617 2.02137 4.09849 2 4.00989 2C3.73375 2 3.50989 2.22386 3.50989 2.5C3.50989 3.69999 3.13456 5.20523 2.56749 6.26595C2.53126 6.33371 2.50989 6.4114 2.50989 6.5C2.50989 6.77614 2.73375 7 3.00989 7C4.11754 7 5.1242 7.67729 5.54744 8.69182C5.62443 8.87636 5.80505 9 6.00989 9C6.21472 9 6.39535 8.87636 6.47234 8.69181C6.89558 7.67728 7.90224 7 9.00989 7C9.28603 7 9.50989 6.77614 9.50989 6.5C9.50989 6.38517 9.47359 6.28459 9.41099 6.20123C8.83362 5.43226 8.50989 4.46105 8.50989 3.5C8.50989 3.22386 8.28603 3 8.00989 3C6.8099 3 5.30466 2.62468 4.24394 2.0576ZM5.18687 0.293835C5.95626 0.705162 7.13745 1 8.00989 1C9.3906 1 10.5099 2.11929 10.5099 3.5C10.5099 4.02821 10.6932 4.57797 11.0103 5.00037C11.324 5.41814 11.5099 5.93736 11.5099 6.5C11.5099 7.88071 10.3906 9 9.00989 9C8.7135 9 8.43227 9.18832 8.31815 9.46185C7.94133 10.3651 7.04975 11 6.00989 11C4.97003 11 4.07845 10.3651 3.70162 9.46186C3.58751 9.18832 3.30627 9 3.00989 9C1.62918 9 0.509888 7.88071 0.509888 6.5C0.509888 6.07447 0.616203 5.67377 0.803721 5.32302C1.21505 4.55363 1.50989 3.37244 1.50989 2.5C1.50989 1.11929 2.62918 0 4.00989 0C4.43542 0 4.83612 0.106316 5.18687 0.293835Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M6.50989 9C7.06217 9 7.50989 9.44772 7.50989 10V14C7.50989 14.5523 7.06217 15 6.50989 15C5.9576 15 5.50989 14.5523 5.50989 14V10C5.50989 9.44772 5.9576 9 6.50989 9Z" fill="black"/><path fill-rule="evenodd" clip-rule="evenodd" d="M5.69037 11.2631C6.91125 10.5449 8.33713 10 11.0099 10C12.1728 10 13.1766 10.2897 13.8813 10.5715C14.2359 10.7134 14.5219 10.8561 14.7231 10.9659C14.8239 11.0208 14.904 11.0679 14.9615 11.1028C14.9902 11.1203 15.0133 11.1348 15.0306 11.1458L15.0521 11.1597L15.0595 11.1645L15.0623 11.1664L15.0635 11.1672C15.0638 11.1674 15.0646 11.1679 14.5099 12L15.0646 11.1679C15.5241 11.4743 15.6483 12.0952 15.3419 12.5547C15.0364 13.0131 14.4179 13.1378 13.9587 12.8344C13.9579 12.8339 13.9571 12.8333 13.9562 12.8327C13.9561 12.8327 13.9563 12.8328 13.9562 12.8327C13.9571 12.8333 13.9579 12.8339 13.9587 12.8344L13.9545 12.8317C13.9488 12.8281 13.9378 12.8211 13.9216 12.8112C13.8892 12.7915 13.8365 12.7604 13.7654 12.7216C13.6229 12.6439 13.4088 12.5366 13.1385 12.4285C12.5932 12.2103 11.8469 12 11.0099 12C8.68264 12 7.60853 12.4551 6.70441 12.9869C6.50199 13.106 6.29346 13.2402 6.07164 13.3829C5.26577 13.9015 4.28459 14.533 2.78461 14.9615C2.25357 15.1132 1.70009 14.8058 1.54836 14.2747C1.39664 13.7437 1.70413 13.1902 2.23517 13.0385C3.42625 12.6982 4.10694 12.262 4.87424 11.7704C5.12939 11.6069 5.39411 11.4373 5.69037 11.2631Z" fill="black"/></svg>' ],
        [ 'id' => 7, 'label' => 'Energie und Klima', 'icon' => '<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M11 8.00003C11 9.65688 9.65688 11 8.00003 11C6.34317 11 5.00003 9.65688 5.00003 8.00003C5.00003 6.34317 6.34317 5.00003 8.00003 5.00003C9.65688 5.00003 11 6.34317 11 8.00003Z" fill="black"/><path d="M7.00024 1.5C7.00024 1.22386 7.2241 1 7.50024 1H8.50024C8.77639 1 9.00024 1.22386 9.00024 1.5V3.5C9.00024 3.77614 8.77639 4 8.50024 4H7.50024C7.2241 4 7.00024 3.77614 7.00024 3.5V1.5Z" fill="black"/><path d="M7.00024 12.5C7.00024 12.2239 7.2241 12 7.50024 12H8.50024C8.77639 12 9.00024 12.2239 9.00024 12.5V14.5C9.00024 14.7761 8.77639 15 8.50024 15H7.50024C7.2241 15 7.00024 14.7761 7.00024 14.5V12.5Z" fill="black"/><path d="M14.5002 7C14.7764 7 15.0002 7.22386 15.0002 7.5V8.5C15.0002 8.77614 14.7764 9 14.5002 9H12.5002C12.2241 9 12.0002 8.77614 12.0002 8.5V7.5C12.0002 7.22386 12.2241 7 12.5002 7H14.5002Z" fill="black"/><path d="M3.50024 7C3.77639 7 4.00024 7.22386 4.00024 7.5V8.5C4.00024 8.77614 3.77639 9 3.50024 9H1.50024C1.2241 9 1.00024 8.77614 1.00024 8.5L1.00024 7.5C1.00024 7.22386 1.2241 7 1.50024 7H3.50024Z" fill="black"/><path d="M11.8892 2.69681C12.0845 2.50154 12.401 2.50154 12.5963 2.69681L13.3034 3.40391C13.4987 3.59917 13.4987 3.91576 13.3034 4.11102L11.8892 5.52523C11.6939 5.72049 11.3773 5.72049 11.1821 5.52523L10.475 4.81813C10.2797 4.62286 10.2797 4.30628 10.475 4.11102L11.8892 2.69681Z" fill="black"/><path d="M4.11102 10.475C4.30628 10.2797 4.62286 10.2797 4.81813 10.475L5.52523 11.1821C5.7205 11.3773 5.7205 11.6939 5.52523 11.8892L4.11102 13.3034C3.91576 13.4987 3.59917 13.4987 3.40391 13.3034L2.69681 12.5963C2.50154 12.401 2.50154 12.0845 2.69681 11.8892L4.11102 10.475Z" fill="black"/><path d="M13.3034 11.8892C13.4987 12.0845 13.4987 12.401 13.3034 12.5963L12.5963 13.3034C12.401 13.4987 12.0845 13.4987 11.8892 13.3034L10.475 11.8892C10.2797 11.6939 10.2797 11.3773 10.475 11.1821L11.1821 10.475C11.3773 10.2797 11.6939 10.2797 11.8892 10.475L13.3034 11.8892Z" fill="black"/><path d="M5.52523 4.11102C5.7205 4.30628 5.7205 4.62286 5.52523 4.81813L4.81813 5.52523C4.62286 5.72049 4.30628 5.72049 4.11102 5.52523L2.69681 4.11102C2.50154 3.91576 2.50154 3.59917 2.69681 3.40391L3.40391 2.6968C3.59918 2.50154 3.91576 2.50154 4.11102 2.6968L5.52523 4.11102Z" fill="black"/></svg>' ],
    ];

    protected static $projectTypesOptions = [
        [ 'id' => 1, 'label' => 'Grundlagen und Analysen' ],
        [ 'id' => 2, 'label' => 'Strategien und Planungen' ],
        [ 'id' => 3, 'label' => 'Infrastruktur und bauliche Entwicklung' ],
        [ 'id' => 4, 'label' => 'Produkte und Dienstleistungen' ],
        [ 'id' => 5, 'label' => 'Vermarktung und Betrieb' ],
    ];

    protected static $supportTypesOptions = [
        [ 'id' => 1, 'label' => 'à-fonds-perdu' ],
        [ 'id' => 2, 'label' => 'Darlehen und Investitionskredit' ],
        [ 'id' => 3, 'label' => 'Bürgschaft' ],
        [ 'id' => 4, 'label' => 'Steuererleichterung' ],
        [ 'id' => 5, 'label' => 'Abgeltung' ],
    ];

    protected static $regionsOptions = [
        [ 'id' => 1, 'label' => 'Ganze Schweiz' ],
        [ 'id' => 2, 'label' => 'Stadt und Agglomeration' ],
        [ 'id' => 3, 'label' => 'Ländlicher Raum' ],
        [ 'id' => 4, 'label' => 'Berggebiet' ],
        [ 'id' => 5, 'label' => 'Grenzgebiet' ],
    ];

    protected function translateField ($id, $field, $locale, $default = '', $objectClass = 'tpoint\RegiosuisseBundle\Entity\Muef') {

        $translation = $this->connection->fetchAssociative('SELECT * FROM ext_translations WHERE foreign_key = '.$id.' AND object_class = :object_class AND field = "'.$field.'" AND locale = "'.$locale.'"', [
            'object_class' => $objectClass
        ]);

        if(!$translation) {
            return $default;
        }

        return $translation['content'];

    }

    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        if(!count($this->connection->fetchAllAssociative('SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME LIKE "muef"'))) {
            return;
        }

        $muefs = $this->connection->fetchAllAssociative('SELECT * FROM muef ORDER BY sort');

        foreach($muefs as $muef) {

            $financialSupport = [];
            $financialSupport['name'] = $this->translateField($muef['id'], 'name', 'de', $muef['name']);
            $financialSupport['logo'] = null;
            $financialSupport['description'] = $this->translateField($muef['id'], 'description', 'de', $muef['description']);
            $financialSupport['created_at'] = date('Y-m-d H:i:s');
            $financialSupport['is_public'] = $muef['public'];
            $financialSupport['position'] = $muef['sort'];
            $financialSupport['policies'] = $this->translateField($muef['id'], 'policies', 'de', $muef['policies']);
            $financialSupport['additional_information'] = $this->translateField($muef['id'], 'additionalInformation', 'de', $muef['additionalInformation']);
            $financialSupport['inclusion_criteria'] = $this->translateField($muef['id'], 'inclusionCriteria', 'de', $muef['inclusionCriteria']);
            $financialSupport['exclusion_criteria'] = $this->translateField($muef['id'], 'exclusionCriteria', 'de', $muef['exclusionCriteria']);
            $financialSupport['application'] = $this->translateField($muef['id'], 'application', 'de', $muef['application']);
            $financialSupport['financing_ratio'] = $this->translateField($muef['id'], 'financingRatio', 'de', $muef['financingRatio']);
            $financialSupport['res'] = $this->translateField($muef['id'], 'res', 'de', $muef['res']);
            $financialSupport['start_date'] = $muef['startdate'];
            $financialSupport['end_date'] = $muef['enddate'];
            $financialSupport['links'] = [];
            $financialSupport['contacts'] = [];
            $financialSupport['translations'] = [
                'fr' => [
                    'links' => [],
                    'contacts' => [],
                ],
                'it' => [
                    'links' => [],
                    'contacts' => [],
                ],
            ];

            foreach(['fr', 'it'] as $locale) {

                if(!array_key_exists($locale, $financialSupport['translations'])) {
                    $financialSupport['translations'][$locale] = [];
                }

                foreach([
                    'name', 'description', 'policies', 'additional_information', 'inclusion_criteria',
                    'exclusion_criteria', 'application', 'financing_ratio', 'res',
                ] as $field) {

                    $capitalizedField = str_replace(' ', '', ucwords(str_replace('_', ' ', $field)));
                    $capitalizedField[0] = strtolower($capitalizedField)[0];

                    if($this->translateField($muef['id'], $capitalizedField, 'de', $financialSupport[$field]) === $this->translateField($muef['id'], $capitalizedField, $locale, $financialSupport[$field])) {
                        continue;
                    }

                    $financialSupport['translations'][$locale][$capitalizedField] = $this->translateField($muef['id'], $capitalizedField, $locale, $financialSupport[$field]);

                }

            }

            $authorities = [];
            $states = [];
            $beneficiaries = [];
            $topics = [];
            $projectTypes = [];
            $instruments = [];
            $geographicRegions = [];

            if($muef['logoString']) {

                $logo = [
                    'data' => $this->translateField($muef['id'], 'logoString', 'de', $muef['logoString']),
                    'created_at' => date('Y-m-d H:i:s'),
                    'hash' => md5($this->translateField($muef['id'], 'logoString', 'de', $muef['logoString'])),
                ];

                if(str_starts_with($muef['logoString'], 'data:image/jpeg')) {
                    $logo['mime_type'] = 'image/jpeg';
                    $logo['extension'] = 'jpg';
                }

                if(str_starts_with($muef['logoString'], 'data:image/png')) {
                    $logo['mime_type'] = 'image/png';
                    $logo['extension'] = 'png';
                }

                if(str_starts_with($muef['logoString'], 'data:image/gif')) {
                    $logo['mime_type'] = 'image/gif';
                    $logo['extension'] = 'gif';
                }

                $logo['name'] = 'logo-'.$muef['id'].'.'.$logo['extension'];

                $this->connection->insert('pv_file', $logo);

                $financialSupport['logo'] = [
                    'id' => $this->connection->lastInsertId(),
                    'name' => $logo['name'],
                    'extension' => $logo['extension'],
                    'mimeType' => $logo['mime_type'],
                    'copyright' => '',
                    'description' => '',
                ];

                foreach(['fr', 'it'] as $locale) {

                    if($this->translateField($muef['id'], 'logoString', 'de', $muef['logoString']) === $this->translateField($muef['id'], 'logoString', $locale, $muef['logoString'])) {
                        continue;
                    }

                    $logo = [
                        'data' => $this->translateField($muef['id'], 'logoString', $locale, $muef['logoString']),
                        'created_at' => date('Y-m-d H:i:s'),
                        'hash' => md5($this->translateField($muef['id'], 'logoString', $locale, $muef['logoString'])),
                    ];

                    if(str_starts_with($muef['logoString'], 'data:image/jpeg')) {
                        $logo['mime_type'] = 'image/jpeg';
                        $logo['extension'] = 'jpg';
                    }

                    if(str_starts_with($muef['logoString'], 'data:image/png')) {
                        $logo['mime_type'] = 'image/png';
                        $logo['extension'] = 'png';
                    }

                    if(str_starts_with($muef['logoString'], 'data:image/gif')) {
                        $logo['mime_type'] = 'image/gif';
                        $logo['extension'] = 'gif';
                    }

                    $logo['name'] = 'logo-'.$muef['id'].'.'.$logo['extension'];

                    $this->connection->insert('pv_file', $logo);

                    if(!array_key_exists($locale, $financialSupport['translations'])) {
                        $financialSupport['translations'][$locale] = [];
                    }

                    $financialSupport['translations'][$locale]['logo'] = [
                        'id' => $this->connection->lastInsertId(),
                        'name' => $logo['name'],
                        'extension' => $logo['extension'],
                        'mimeType' => $logo['mime_type'],
                        'copyright' => '',
                        'description' => '',
                    ];

                }

            }

            $moreInformations = $this->connection->fetchAllAssociative('SELECT * FROM muef_more_information WHERE muef_id = '.$muef['id'].' ORDER BY position');

            foreach($moreInformations as $moreInformation) {

                $financialSupport['links'][] = [
                    'label' => $this->translateField($moreInformation['id'], 'name', 'de', $moreInformation['name'], 'tpoint\RegiosuisseBundle\Entity\MoreInformation'),
                    'value' => $this->translateField($moreInformation['id'], 'url', 'de', $moreInformation['url'], 'tpoint\RegiosuisseBundle\Entity\MoreInformation'),
                ];

                foreach(['fr', 'it'] as $locale) {

                    if(!array_key_exists($locale, $financialSupport['translations'])) {
                        $financialSupport['translations'][$locale]['links'] = [];
                    }

                    if(!array_key_exists('links', $financialSupport['translations'][$locale])) {
                        $financialSupport['translations'][$locale]['links'] = [];
                    }

                    $financialSupport['translations'][$locale]['links'][] = [
                        'label' => $this->translateField($moreInformation['id'], 'name', $locale, $moreInformation['name'], 'tpoint\RegiosuisseBundle\Entity\MoreInformation'),
                        'value' => $this->translateField($moreInformation['id'], 'url', $locale, $moreInformation['url'], 'tpoint\RegiosuisseBundle\Entity\MoreInformation'),
                    ];

                }

            }

            $externalProjects = $this->connection->fetchAllAssociative('SELECT * FROM muef_external_project WHERE muef_id = '.$muef['id'].' ORDER BY position');

            foreach($externalProjects as $externalProject) {

                $financialSupport['links'][] = [
                    'label' => $this->translateField($externalProject['id'], 'name', 'de', $externalProject['name'], 'tpoint\RegiosuisseBundle\Entity\ExternalProject'),
                    'value' => $this->translateField($externalProject['id'], 'url', 'de', $externalProject['url'], 'tpoint\RegiosuisseBundle\Entity\ExternalProject'),
                ];

                foreach(['fr', 'it'] as $locale) {

                    if(!array_key_exists($locale, $financialSupport['translations'])) {
                        $financialSupport['translations'][$locale]['links'] = [];
                    }

                    if(!array_key_exists('links', $financialSupport['translations'][$locale])) {
                        $financialSupport['translations'][$locale]['links'] = [];
                    }

                    $financialSupport['translations'][$locale]['links'][] = [
                        'label' => $this->translateField($externalProject['id'], 'name', $locale, $externalProject['name'], 'tpoint\RegiosuisseBundle\Entity\ExternalProject'),
                        'value' => $this->translateField($externalProject['id'], 'url', $locale, $externalProject['url'], 'tpoint\RegiosuisseBundle\Entity\ExternalProject'),
                    ];

                }

            }

            $muefContacts = $this->connection->fetchAllAssociative('SELECT * FROM muef_contact WHERE muef_id = '.$muef['id'].' ORDER BY position');

            foreach($muefContacts as $muefContact) {

                foreach(['de', 'fr', 'it'] as $locale) {

                    $contact = $this->connection->fetchAssociative('SELECT * FROM contact WHERE id = '.$muefContact['contact_id']);

                    $contactData = [];
                    $contactData['foreignId'] = $muefContact['contact_id'];
                    $contactData['name'] = '';
                    $contactData['title'] = '';
                    $contactData['firstName'] = '';
                    $contactData['lastName'] = '';
                    $contactData['salutation'] = '';
                    $contactData['role'] = '';
                    $contactData['street'] = '';
                    $contactData['zipCode'] = '';
                    $contactData['city'] = '';

                    if($contact['type'] === 'company') {
                        $contact = array_merge($contact, $this->connection->fetchAssociative('SELECT * FROM company WHERE id = '.$muefContact['contact_id']));
                    }

                    if($contact['type'] === 'person') {
                        $contact = array_merge($contact, $this->connection->fetchAssociative('SELECT * FROM person WHERE id = '.$muefContact['contact_id']));

                        $company = $this->connection->fetchAssociative('SELECT * FROM company_person WHERE person_id = '.$muefContact['contact_id'].' ORDER BY company_id DESC LIMIT 1');

                        if($company) {
                            $contact = array_merge($contact, $this->connection->fetchAssociative('SELECT * FROM company WHERE id = '.$company['company_id']));
                        }
                    }

                    $contactData['name'] = $this->translateField($contact['id'], 'companyName', $locale, $contact['companyName'] ?? $contactData['name'], 'tpoint\ContactBundle\Entity\Contact');
                    $contactData['title'] = $this->translateField($contact['id'], 'namePrefix', $locale, $contact['namePrefix'] ?? $contactData['title'], 'tpoint\ContactBundle\Entity\Contact');
                    $contactData['firstName'] = $contact['forename'] ?? $contactData['firstName'];
                    $contactData['lastName'] = $contact['surname'] ?? $contactData['lastName'];
                    $contactData['salutation'] = $contact['gender'] ?? $contactData['salutation'];
                    $contactData['role'] = $this->translateField($contact['id'], 'businessFunction', $locale, $contact['businessFunction'] ?? $contactData['role'], 'tpoint\ContactBundle\Entity\Contact');
                    $contactData['street'] = $this->translateField($contact['id'], 'address', $locale, $contact['address'] ?? $contactData['street'], 'tpoint\ContactBundle\Entity\Contact');
                    $contactData['zipCode'] = $this->translateField($contact['id'], 'zipCode', $locale, $contact['zipCode'] ?? $contactData['zipCode'], 'tpoint\ContactBundle\Entity\Contact');
                    $contactData['city'] = $this->translateField($contact['id'], 'city', $locale, $contact['city'] ?? $contactData['city'], 'tpoint\ContactBundle\Entity\Contact');

                    $contactInformations = $this->connection->fetchAllAssociative('SELECT * FROM contactInformation WHERE contact_id = '.$muefContact['contact_id']);

                    foreach($contactInformations as $contactInformation) {

                        $informationGroup = $this->connection->fetchAssociative('SELECT * FROM informationGroup WHERE id = '.$contactInformation['informationGroup_id']);

                        if($informationGroup['type'] === 'phone') {
                            $contactData['phone'] = $contactData['phone'] ?? $contactInformation['value'];
                        }

                        if($informationGroup['type'] === 'email') {
                            $contactData['email'] = $contactData['email'] ?? $contactInformation['value'];
                        }

                        if($informationGroup['type'] === 'http' || $informationGroup['type'] === 'https') {
                            $contactData['website'] = $contactData['website'] ?? $contactInformation['value'];
                        }

                        if($informationGroup['type'] === 'fax') {
                            $contactData['fax'] = $contactData['fax'] ?? $contactInformation['value'];
                        }

                    }

                    if(!$this->connection->fetchAssociative('SELECT m.* FROM muef_contact_language AS m LEFT JOIN language AS l ON (m.language_id = l.id) WHERE m.muef_contact_id = '.$muefContact['id'].' AND l.code = "'.$locale.'"')) {
                        continue;
                    }

                    if($locale === 'de') {
                        $financialSupport['contacts'][] = $contactData;
                        continue;
                    }

                    $financialSupport['translations'][$locale]['contacts'][] = $contactData;

                }

            }

            $muefProvinces = $this->connection->fetchAllAssociative('SELECT * FROM muef_province WHERE muef_id = '.$muef['id'].'');

            foreach($muefProvinces as $muefProvince) {

                $province = $this->connection->fetchAssociative('SELECT * FROM province WHERE id = '.$muefProvince['province_id'].'');

                $state = $this->connection->fetchAssociative('SELECT * FROM pv_state WHERE name = :name', [
                    'name' => $province['label'],
                ]);

                if(!$state) {
                    continue;
                }

                $states[] = $state['id'];

            }

            $muefRelations = $muef['authorities'] ? explode(',', $muef['authorities']) : [];

            foreach($muefRelations as $muefRelation) {

                $relationOption = null;

                foreach(self::$authoritiesOptions as $option) {

                    if($option['id'] === intval($muefRelation)) {
                        $relationOption = $option;
                        break;
                    }

                }

                if(!$relationOption) {
                    continue;
                }

                $relation = $this->connection->fetchAssociative('SELECT * FROM pv_authority WHERE context = "financial-support" AND name = "'.$relationOption['label'].'"');

                if($relation) {
                    $authorities[] = $relation['id'];
                    continue;
                }

                $this->connection->insert('pv_authority', [
                    'name' => $relationOption['label'],
                    'is_public' => 1,
                    'is_state_supported' => $relationOption['supportsProvinces'] ? 1 : 0,
                    'created_at' => date('Y-m-d H:i:s'),
                    'synonyms' => json_encode([]),
                    'translations' => json_encode(['fr' => '', 'it' => '']),
                    'context' => 'financial-support',
                ]);

                $authorities[] = $this->connection->lastInsertId();

            }

            $muefRelations = $muef['beneficiaries'] ? explode(',', $muef['beneficiaries']) : [];

            foreach($muefRelations as $muefRelation) {

                $relationOption = null;

                foreach(self::$beneficiariesOptions as $option) {

                    if($option['id'] === intval($muefRelation)) {
                        $relationOption = $option;
                        break;
                    }

                }

                if(!$relationOption) {
                    continue;
                }

                $relation = $this->connection->fetchAssociative('SELECT * FROM pv_beneficiary WHERE context = "financial-support" AND name = "'.$relationOption['label'].'"');

                if($relation) {
                    $beneficiaries[] = $relation['id'];
                    continue;
                }

                $this->connection->insert('pv_beneficiary', [
                    'name' => $relationOption['label'],
                    'is_public' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'synonyms' => json_encode([]),
                    'translations' => json_encode(['fr' => '', 'it' => '']),
                    'context' => 'financial-support',
                ]);

                $beneficiaries[] = $this->connection->lastInsertId();

            }

            $muefRelations = $muef['topics'] ? explode(',', $muef['topics']) : [];

            foreach($muefRelations as $muefRelation) {

                $relationOption = null;

                foreach(self::$topicsOptions as $option) {

                    if($option['id'] === intval($muefRelation)) {
                        $relationOption = $option;
                        break;
                    }

                }

                if(!$relationOption) {
                    continue;
                }

                $relation = $this->connection->fetchAssociative('SELECT * FROM pv_topic WHERE context = "financial-support" AND name = "'.$relationOption['label'].'"');

                if($relation) {
                    $topics[] = $relation['id'];
                    continue;
                }

                $this->connection->insert('pv_topic', [
                    'name' => $relationOption['label'],
                    'is_public' => 1,
                    'icon' => $relationOption['icon'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'synonyms' => json_encode([]),
                    'translations' => json_encode(['fr' => '', 'it' => '']),
                    'context' => 'financial-support',
                ]);

                $topics[] = $this->connection->lastInsertId();

            }

            $muefRelations = $muef['projectTypes'] ? explode(',', $muef['projectTypes']) : [];

            foreach($muefRelations as $muefRelation) {

                $relationOption = null;

                foreach(self::$projectTypesOptions as $option) {

                    if($option['id'] === intval($muefRelation)) {
                        $relationOption = $option;
                        break;
                    }

                }

                if(!$relationOption) {
                    continue;
                }

                $relation = $this->connection->fetchAssociative('SELECT * FROM pv_project_type WHERE context = "financial-support" AND name = "'.$relationOption['label'].'"');

                if($relation) {
                    $projectTypes[] = $relation['id'];
                    continue;
                }

                $this->connection->insert('pv_project_type', [
                    'name' => $relationOption['label'],
                    'is_public' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'synonyms' => json_encode([]),
                    'translations' => json_encode(['fr' => '', 'it' => '']),
                    'context' => 'financial-support',
                ]);

                $projectTypes[] = $this->connection->lastInsertId();

            }

            $muefRelations = $muef['supportTypes'] ? explode(',', $muef['supportTypes']) : [];

            foreach($muefRelations as $muefRelation) {

                $relationOption = null;

                foreach(self::$supportTypesOptions as $option) {

                    if($option['id'] === intval($muefRelation)) {
                        $relationOption = $option;
                        break;
                    }

                }

                if(!$relationOption) {
                    continue;
                }

                $relation = $this->connection->fetchAssociative('SELECT * FROM pv_instrument WHERE context = "financial-support" AND name = "'.$relationOption['label'].'"');

                if($relation) {
                    $instruments[] = $relation['id'];
                    continue;
                }

                $this->connection->insert('pv_instrument', [
                    'name' => $relationOption['label'],
                    'is_public' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'synonyms' => json_encode([]),
                    'translations' => json_encode(['fr' => '', 'it' => '']),
                    'context' => 'financial-support',
                ]);

                $instruments[] = $this->connection->lastInsertId();

            }

            $muefRelations = $muef['regions'] ? explode(',', $muef['regions']) : [];

            foreach($muefRelations as $muefRelation) {

                $relationOption = null;

                foreach(self::$regionsOptions as $option) {

                    if($option['id'] === intval($muefRelation)) {
                        $relationOption = $option;
                        break;
                    }

                }

                if(!$relationOption) {
                    continue;
                }

                $relation = $this->connection->fetchAssociative('SELECT * FROM pv_geographic_region WHERE context = "financial-support" AND name = "'.$relationOption['label'].'"');

                if($relation) {
                    $geographicRegions[] = $relation['id'];
                    continue;
                }

                $this->connection->insert('pv_geographic_region', [
                    'name' => $relationOption['label'],
                    'is_public' => 1,
                    'created_at' => date('Y-m-d H:i:s'),
                    'synonyms' => json_encode([]),
                    'translations' => json_encode(['fr' => '', 'it' => '']),
                    'context' => 'financial-support',
                ]);

                $geographicRegions[] = $this->connection->lastInsertId();

            }

            $this->connection->insert('pv_financial_support', array_merge($financialSupport, [
                'logo' => json_encode($financialSupport['logo']),
                'links' => json_encode($financialSupport['links']),
                'contacts' => json_encode($financialSupport['contacts']),
                'translations' => json_encode($financialSupport['translations']),
            ]));

            $financialSupport['id'] = $this->connection->lastInsertId();

            foreach($authorities as $authority) {
                $this->connection->insert('pv_financial_support_authority', [
                    'financial_support_id' => $financialSupport['id'],
                    'authority_id' => $authority,
                ]);
            }

            foreach($states as $state) {
                $this->connection->insert('pv_financial_support_state', [
                    'financial_support_id' => $financialSupport['id'],
                    'state_id' => $state,
                ]);
            }

            foreach($beneficiaries as $beneficiary) {
                $this->connection->insert('pv_financial_support_beneficiary', [
                    'financial_support_id' => $financialSupport['id'],
                    'beneficiary_id' => $beneficiary,
                ]);
            }

            foreach($topics as $topic) {
                $this->connection->insert('pv_financial_support_topic', [
                    'financial_support_id' => $financialSupport['id'],
                    'topic_id' => $topic,
                ]);
            }

            foreach($projectTypes as $projectType) {
                $this->connection->insert('pv_financial_support_project_type', [
                    'financial_support_id' => $financialSupport['id'],
                    'project_type_id' => $projectType,
                ]);
            }

            foreach($instruments as $instrument) {
                $this->connection->insert('pv_financial_support_instrument', [
                    'financial_support_id' => $financialSupport['id'],
                    'instrument_id' => $instrument,
                ]);
            }

            foreach($geographicRegions as $geographicRegion) {
                $this->connection->insert('pv_financial_support_geographic_region', [
                    'financial_support_id' => $financialSupport['id'],
                    'geographic_region_id' => $geographicRegion,
                ]);
            }

        }
    }

    public function down(Schema $schema): void
    {

    }
}
