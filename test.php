<?php

/**
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

include __DIR__ . '/vendor/autoload.php';

$body = <<< 'EOF'
    <?xml version="1.0" encoding="utf-8"?>
    <cas:serviceResponse xmlns:cas="https://ecas.ec.europa.eu/cas/schemas/" date="" version="">
        <cas:authenticationSuccess>
            <cas:user>lauredo</cas:user>
            <cas:departmentNumber>DIGIT.A.3</cas:departmentNumber>
            <cas:email>lauredo@gmail.com</cas:email>
            <cas:employeeNumber>12345</cas:employeeNumber>
            <cas:employeeType>n</cas:employeeType>
            <cas:firstName>Dominique</cas:firstName>
            <cas:lastName>LAURENT</cas:lastName>
            <cas:domain>external</cas:domain>
            <cas:domainUsername>MlauredoM</cas:domainUsername>
            <cas:telephoneNumber>98765</cas:telephoneNumber>
            <cas:userManager>uid=poelsfc,dc=commission,dc=eu</cas:userManager>
            <cas:timeZone>GMT</cas:timeZone>
            <cas:locale>fr</cas:locale>
            <cas:assuranceLevel>10</cas:assuranceLevel>
            <cas:uid>nlauredo</cas:uid>
            <cas:orgId>123456</cas:orgId>
            <cas:teleworkingPriority>true</cas:teleworkingPriority>
            <cas:extendedAttributes>
                <cas:extendedAttribute name="http://stork.eu/motherInLawDogName">
                    <cas:attributeValue>rex</cas:attributeValue>
                    <cas:attributeValue>snoopy</cas:attributeValue>
                </cas:extendedAttribute>
            </cas:extendedAttributes>
            <cas:groups number="2">
                <cas:group>INTERNET</cas:group>
                <cas:group>LIVENEWS</cas:group>
            </cas:groups>
            <cas:strengths number="1">
                <cas:strength>STRONG</cas:strength>
            </cas:strengths>
            <cas:authenticationFactors number="1">
                <cas:moniker>MverfadeM</cas:moniker>
            </cas:authenticationFactors>
            <cas:loginDate></cas:loginDate>
            <cas:sso>true</cas:sso>
            <cas:ticketType>PROXY</cas:ticketType>
            <cas:proxyGrantingProtocol>PGT_URL</cas:proxyGrantingProtocol>
            <cas:proxies>
                <cas:proxy>https://callbackUrl.eu</cas:proxy>
            </cas:proxies>
        </cas:authenticationSuccess>
    </cas:serviceResponse>
    EOF;

$response = new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/xml'], $body);

$introspector = new \EcPhp\CasLib\Introspection\Introspector();

$ir = $introspector->detect($response);

var_export($ir->getParsedResponse());
