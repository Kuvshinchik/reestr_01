<?php
namespace Database\Seeders; // см. пункт 2 ниже

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ObjectcategorieszimaSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('objectcategorieszima')->insert([
           
			[
'name' => ' План Здания вокзалов','slug' => 'plan_zdaniya_vokzalov',
],

[
'name' => ' Факт Здания вокзалов','slug' => 'fakt_zdaniya_vokzalov',
],

[
'name' => ' План Кровля','slug' => 'plan_krovlya',
],

[
'name' => ' Факт Кровля','slug' => 'fakt_krovlya',
],

[
'name' => ' План Желоба и водостоки','slug' => 'plan_zheloba_i_vodostoki',
],

[
'name' => ' Факт Желоба и водостоки','slug' => 'fakt_zheloba_i_vodostoki',
],

[
'name' => ' План Входные группы','slug' => 'plan_vkhodnye_gruppy',
],

[
'name' => ' Факт Входные группы','slug' => 'fakt_vkhodnye_gruppy',
],

[
'name' => ' План Оконные блоки','slug' => 'plan_okonnye_bloki',
],

[
'name' => ' Факт Оконные блоки','slug' => 'fakt_okonnye_bloki',
],

[
'name' => ' План Системы вентиляции','slug' => 'plan_sistemy_ventilyatsii',
],

[
'name' => ' Факт Системы вентиляции','slug' => 'fakt_sistemy_ventilyatsii',
],

[
'name' => ' План Тепловые завесы','slug' => 'plan_teplovye_zavesy',
],

[
'name' => ' Факт Тепловые завесы','slug' => 'fakt_teplovye_zavesy',
],

[
'name' => ' План Инфракрасные обогреватели','slug' => 'plan_infrakrasnye_obogrevateli',
],

[
'name' => ' Факт Инфракрасные обогреватели','slug' => 'fakt_infrakrasnye_obogrevateli',
],

[
'name' => ' План Источники резервного питания','slug' => 'plan_istochniki_rezervnogo_pitaniya',
],

[
'name' => ' Факт Источники резервного питания','slug' => 'fakt_istochniki_rezervnogo_pitaniya',
],

[
'name' => ' План Дизель-генераторные установки','slug' => 'plan_dizel_generatornye_ustanovki',
],

[
'name' => ' Факт Дизель-генераторные установки','slug' => 'fakt_dizel_generatornye_ustanovki',
],

[
'name' => ' План Источники бесперебойного питания, обеспечивающие питание основного электрооборудования вокзала','slug' => 'plan_istochniki_bespereboynogo_pitaniya_obespechivayuschie_pitanie_osnovnogo_elektrooborudovaniya_vokzala',
],

[
'name' => ' Факт Источники бесперебойного питания, обеспечивающие питание основного электрооборудования вокзала','slug' => 'fakt_istochniki_bespereboynogo_pitaniya_obespechivayuschie_pitanie_osnovnogo_elektrooborudovaniya_vokzala',
],

[
'name' => ' План Тепловые пункты','slug' => 'plan_teplovye_punkty',
],

[
'name' => ' Факт Тепловые пункты','slug' => 'fakt_teplovye_punkty',
],

[
'name' => ' План Промывка, опресовка и наладка работы системы по договору','slug' => 'plan_promyvka_opresovka_i_naladka_raboty_sistemy_po_dogovoru',
],

[
'name' => ' Факт Промывка, опресовка и наладка работы системы по договору','slug' => 'fakt_promyvka_opresovka_i_naladka_raboty_sistemy_po_dogovoru',
],

[
'name' => ' План Промывка, опресовка и наладка работы системы собственными силами','slug' => 'plan_promyvka_opresovka_i_naladka_raboty_sistemy_sobstvennymi_silami',
],

[
'name' => ' Факт Промывка, опресовка и наладка работы системы собственными силами','slug' => 'fakt_promyvka_opresovka_i_naladka_raboty_sistemy_sobstvennymi_silami',
],

[
'name' => ' План Котлы (электро, газовые)','slug' => 'plan_kotly_elektro_gazovye',
],

[
'name' => ' Факт Котлы (электро, газовые)','slug' => 'fakt_kotly_elektro_gazovye',
],

[
'name' => ' План Котельные (газовые, угольные)','slug' => 'plan_kotelnye_gazovye_ugolnye',
],

[
'name' => ' Факт Котельные (газовые, угольные)','slug' => 'fakt_kotelnye_gazovye_ugolnye',
],

[
'name' => ' План Пешеходные мосты','slug' => 'plan_peshekhodnye_mosty',
],

[
'name' => ' Факт Пешеходные мосты','slug' => 'fakt_peshekhodnye_mosty',
],

[
'name' => ' План В том числе подготовка освещения (система)','slug' => 'plan_v_tom_chisle_podgotovka_osvescheniya_sistema',
],

[
'name' => ' Факт В том числе подготовка освещения (система)','slug' => 'fakt_v_tom_chisle_podgotovka_osvescheniya_sistema',
],

[
'name' => ' План В том числе подготовка освещения (точки)','slug' => 'plan_v_tom_chisle_podgotovka_osvescheniya_tochki',
],

[
'name' => ' Факт В том числе подготовка освещения (точки)','slug' => 'fakt_v_tom_chisle_podgotovka_osvescheniya_tochki',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые собственными силами (системы освещения)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_sistemy_osvescheniya',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые собственными силами (системы освещения)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_sistemy_osvescheniya',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые собственными силами (точки освещения)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_tochki_osvescheniya',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые собственными силами (точки освещения)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_tochki_osvescheniya',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые по договору (системы освещения)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_sistemy_osvescheniya',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые по договору (системы освещения)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_sistemy_osvescheniya',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые по договору (точки освещения)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_tochki_osvescheniya',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые по договору (точки освещения)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_tochki_osvescheniya',
],

[
'name' => ' План На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (системы освещения)','slug' => 'plan_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_sistemy_osvescheniya',
],

[
'name' => ' Факт На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (системы освещения)','slug' => 'fakt_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_sistemy_osvescheniya',
],

[
'name' => ' План На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (точки освещения)','slug' => 'plan_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_tochki_osvescheniya',
],

[
'name' => ' Факт На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (точки освещения)','slug' => 'fakt_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_tochki_osvescheniya',
],

[
'name' => ' План Пешеходные тоннели','slug' => 'plan_peshekhodnye_tonneli',
],

[
'name' => ' Факт Пешеходные тоннели','slug' => 'fakt_peshekhodnye_tonneli',
],

[
'name' => ' План Подготовка освещения (пешеходные тоннели, система)','slug' => 'plan_podgotovka_osvescheniya_peshekhodnye_tonneli_sistema',
],

[
'name' => ' Факт Подготовка освещения (пешеходные тоннели, система)','slug' => 'fakt_podgotovka_osvescheniya_peshekhodnye_tonneli_sistema',
],

[
'name' => ' План Подготовка освещения (пешеходные тоннели, точки)','slug' => 'plan_podgotovka_osvescheniya_peshekhodnye_tonneli_tochki',
],

[
'name' => ' Факт Подготовка освещения (пешеходные тоннели, точки)','slug' => 'fakt_podgotovka_osvescheniya_peshekhodnye_tonneli_tochki',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые собственными силами (пешеходные тоннели, системы)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_peshekhodnye_tonneli_sistemy',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые собственными силами (пешеходные тоннели, системы)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_peshekhodnye_tonneli_sistemy',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые собственными силами (пешеходные тоннели, точки)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_peshekhodnye_tonneli_tochki',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые собственными силами (пешеходные тоннели, точки)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_peshekhodnye_tonneli_tochki',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые по договору (пешеходные тоннели, системы)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_peshekhodnye_tonneli_sistemy',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые по договору (пешеходные тоннели, системы)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_peshekhodnye_tonneli_sistemy',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые по договору (пешеходные тоннели, точки)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_peshekhodnye_tonneli_tochki',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые по договору (пешеходные тоннели, точки)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_peshekhodnye_tonneli_tochki',
],

[
'name' => ' План На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пешеходные тоннели, системы)','slug' => 'plan_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_peshekhodnye_tonneli_sistemy',
],

[
'name' => ' Факт На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пешеходные тоннели, системы)','slug' => 'fakt_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_peshekhodnye_tonneli_sistemy',
],

[
'name' => ' План На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пешеходные тоннели, точки)','slug' => 'plan_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_peshekhodnye_tonneli_tochki',
],

[
'name' => ' Факт На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пешеходные тоннели, точки)','slug' => 'fakt_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_peshekhodnye_tonneli_tochki',
],

[
'name' => ' План Пассажирские платформы','slug' => 'plan_passazhirskie_platformy',
],

[
'name' => ' Факт Пассажирские платформы','slug' => 'fakt_passazhirskie_platformy',
],

[
'name' => ' План Подготовка освещения (пассажирские платформы, система)','slug' => 'plan_podgotovka_osvescheniya_passazhirskie_platformy_sistema',
],

[
'name' => ' Факт Подготовка освещения (пассажирские платформы, система)','slug' => 'fakt_podgotovka_osvescheniya_passazhirskie_platformy_sistema',
],

[
'name' => ' План Подготовка освещения (пассажирские платформы, точки)','slug' => 'plan_podgotovka_osvescheniya_passazhirskie_platformy_tochki',
],

[
'name' => ' Факт Подготовка освещения (пассажирские платформы, точки)','slug' => 'fakt_podgotovka_osvescheniya_passazhirskie_platformy_tochki',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые собственными силами (пассажирские платформы, системы)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_passazhirskie_platformy_sistemy',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые собственными силами (пассажирские платформы, системы)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_passazhirskie_platformy_sistemy',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые собственными силами (пассажирские платформы, точки)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_passazhirskie_platformy_tochki',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые собственными силами (пассажирские платформы, точки)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_sobstvennymi_silami_passazhirskie_platformy_tochki',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые по договору / наряд заказу через НТЭ (пассажирские платформы, системы)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_naryad_zakazu_cherez_nte_passazhirskie_platformy_sistemy',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые по договору / наряд заказу через НТЭ (пассажирские платформы, системы)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_naryad_zakazu_cherez_nte_passazhirskie_platformy_sistemy',
],

[
'name' => ' План На балансе ДЖВ, обслуживаемые по договору / наряд заказу через НТЭ (пассажирские платформы, точки)','slug' => 'plan_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_naryad_zakazu_cherez_nte_passazhirskie_platformy_tochki',
],

[
'name' => ' Факт На балансе ДЖВ, обслуживаемые по договору / наряд заказу через НТЭ (пассажирские платформы, точки)','slug' => 'fakt_na_balanse_dzhv_obsluzhivaemye_po_dogovoru_naryad_zakazu_cherez_nte_passazhirskie_platformy_tochki',
],

[
'name' => ' План На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пассажирские платформы, системы)','slug' => 'plan_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_passazhirskie_platformy_sistemy',
],

[
'name' => ' Факт На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пассажирские платформы, системы)','slug' => 'fakt_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_passazhirskie_platformy_sistemy',
],

[
'name' => ' План На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пассажирские платформы, точки)','slug' => 'plan_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_passazhirskie_platformy_tochki',
],

[
'name' => ' Факт На балансе НТЭ, обслуживаемые по наряд заказу / регламенту (пассажирские платформы, точки)','slug' => 'fakt_na_balanse_nte_obsluzhivaemye_po_naryad_zakazu_reglamentu_passazhirskie_platformy_tochki',
],





			
           
        ]);
    }
}
