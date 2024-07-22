<?php
protected function newGetCode($storehouse,$info, $config)
{
    // 定义获取配置值的函数
    $getConfigValue = function ($areaKey, $typeKey,$fallback) use ($config) {
        return $config[$areaKey][$typeKey] ??$fallback;
    };

    // 定义一个函数来处理重量逻辑
    $handleWeightLogic = function ($areaKey, $typeKey) use ($info, $getConfigValue) {
        if ($info['weight'] > 70) {
            return $getConfigValue($areaKey, 'ahs_weight', 'FEDEX-OS-GROUND');
        } elseif ($info['weight'] >= 47 &&$info['weight'] <= 70) {
            return $getConfigValue($areaKey, 'weight_range', 'FEDEX-WEIGHT-HOME');
        } elseif ($info['weight'] >= 16 &&$info['weight'] <= 46) {
            return $getConfigValue($areaKey, 'normal', 'FEDEX-HOMEDELIVERY');
        }
        return $getConfigValue($areaKey, 'ups', 'UPSGROUND');
    };

    switch ($storehouse['us_area']) {
        case StorehouseConstant::US_WEST:
            if (in_array($info['type'], [3, 4])) {
                $code =$getConfigValue('US_WEST', 'oversize', 'FEDEXOVERSIZE');
            } elseif ($info['type'] == 1) {
                $code =$getConfigValue('US_WEST', 'ahs', 'FEDEX-WEIGHT-HOME');
            } else {
                $code =$handleWeightLogic('US_WEST', 'normal');
            }
            break;
        case StorehouseConstant::US_SOUTH:
            if ($storehouse['name'] == 'KC-GA2') {
                if (in_array($info['type'], [3, 4])) {
                    $code =$getConfigValue('US_SOUTH', 'oversize', 'FEDEXOVERSIZE');
                } elseif ($info['type'] == 1) {
                    $code =$getConfigValue('US_SOUTH', 'ahs', 'GASA-FEDEX-HD');
                } else {
                    $code =$handleWeightLogic('US_SOUTH', 'GA');
                }
            } else {
                $code =$info['weight'] <= 70 && !in_array($info['type'], [3])
                    ? $getConfigValue('US_SOUTH', 'oversize', 'GANW-FEDEX-HD')
                    : $getConfigValue('US_SOUTH', 'normal', 'GANW-FEDEX-GROUND');
            }
            break;
        default:
            if (in_array($info['type'], [3, 4])) {
                $code =$getConfigValue('US_EAST', 'oversize', 'FEDEXOVERSIZE');
            } elseif ($info['type'] == 1) {
                $code =$getConfigValue('US_EAST', 'ahs', 'FEDEX-HD-NJ');
            } else {
                $code =$handleWeightLogic('US_EAST', 'normal');
            }
            break;
    }

    return $code;
}
?>