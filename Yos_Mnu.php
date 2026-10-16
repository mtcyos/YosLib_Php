<?php
// -*- coding: utf-8 -*-
/**
 * Yos_Mnu.php - Definición de la función de renderizado del Menú Global
 */

function Yos_Mnu($Fnc_Mnu)
{
    // Asegurar que la sesión esté iniciada para persistir el menú
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verificar que el array de menú pasado no esté vacío
    if (empty($Fnc_Mnu)) {
        return;
    }

    ?>

    <!-- Estilos CSS para menús y submenús desplegables -->
    <style>
        .navbar { background-color: #f1f1f1; border-bottom: 1px solid #cccccc; display: flex; padding: 0 5px; font-family: Arial, sans-serif; }
        .dropdown { position: relative; display: inline-block; }
        .dropbtn { background-color: inherit; color: #000000; padding: 8px 14px; font-size: 12px; border: none; cursor: pointer; text-transform: uppercase; font-weight: normal; }
        .dropbtn:hover { background-color: #e2e2e2; }

        .dropdown-content { display: none; position: absolute; background-color: #f9f9f9; min-width: 230px; box-shadow: 0px 4px 8px rgba(0,0,0,0.15); z-index: 10; border: 1px solid #cccccc; left: 0; }
        .dropdown-content a { color: #000000; padding: 7px 12px; text-decoration: none; display: block; font-size: 11px; }
        .dropdown-content a:hover { background-color: #e5e5e5; }
        .dropdown-divider { height: 1px; background-color: #dcdcdc; margin: 2px 0; }
        .dropdown:hover .dropdown-content { display: block; }

        /* Estilos para el submenú lateral */
        .dropdown-submenu { position: relative; }
        .dropdown-submenu > a::after { content: " ▸"; float: right; }
        .submenu-content { display: none; position: absolute; left: 100%; top: 0; background-color: #f9f9f9; min-width: 210px; box-shadow: 0px 4px 8px rgba(0,0,0,0.15); border: 1px solid #cccccc; z-index: 11; }
        .dropdown-submenu:hover .submenu-content { display: block; }
    </style>

    <!-- Menú Superior Estilo Sistema -->
    <nav class="navbar">
        <?php foreach ($Fnc_Mnu as $menuKey => $menuGroup): ?>
            <div class="dropdown">
                <button class="dropbtn"><?= htmlspecialchars($menuGroup['Txt']) ?> ▼</button>
                <div class="dropdown-content">
                    <?php foreach ($menuGroup['Sub'] as $subKey => $item): ?>
                        <?php if ($item['Cod'] === 'Sep'): ?>
                            <div class="dropdown-divider"></div>
                        <?php elseif ($item['Cod'] === 'SubMnu'): ?>
                            <div class="dropdown-submenu">
                                <a href="#"><?= htmlspecialchars($item['Txt']) ?></a>
                                <div class="submenu-content">
                                    <?php foreach ($item['Sub'] as $subSubKey => $subItem): ?>
                                        <a href="<?= htmlspecialchars($subItem['Fnc']) ?>">
                                            <?= htmlspecialchars($subItem['Txt']) ?>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <a href="<?= htmlspecialchars($item['Fnc']) ?>">
                                <?= htmlspecialchars($item['Txt']) ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </nav>
    <?php
}
