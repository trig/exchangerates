<?php

namespace App\Services;

use React\Dns\Resolver\Resolver;
use function React\Promise\reject;
use function React\Promise\resolve;

/**
 * To make possible resolve domains that is not publicly available, and defined
 * in /etc/hosts this class was born. For use with React socket onnector
 */
class EtcHostsDnsResolver extends Resolver {

    public function resolve($domain) {
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return resolve($domain);
        }

        $hosts = array_map(function($_) {
            return preg_split('/\s/', trim($_));
        }, array_filter(file('/etc/hosts'), function($_) {
                    if (!trim($_)) {
                        return false;
                    }
                    return true;
                }));

        $resolved = false;
        foreach ($hosts as $hData) {
            if (2 < count($hData) || !filter_var($hData[0], FILTER_VALIDATE_IP)) {
                continue;
            }

            if (!empty($hData[1]) && $domain == $hData[1]) {
                \Log::debug(sprintf('[%s] Resolved [%s] domain to [%s] ip', EtcHostsDnsResolver::class, $domain, $hData[0]));
                $resolved = true;
                return resolve($hData[0]);
            }
        }

        if (!$resolved) {
            \Log::debug(sprintf('[%s] Rejected resolving [%s] domain to [%s] ip', EtcHostsDnsResolver::class, $domain, $hData[0]));
            return reject();
        }
    }

}
