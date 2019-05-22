<?php

class TemplateManager
{
    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);
        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();


        if (isset($data['quote']) && ($data['quote'] instanceof Quote))
        {
            /** @var Quote $quote */
            $quote = $data['quote'];

            $_quoteFromRepository = QuoteRepository::getInstance()->getById($quote->id);
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $destinationOfQuote = DestinationRepository::getInstance()->getById($quote->destinationId);

            $text = $this->computeQuoteDestinationLink($text, $destinationOfQuote, $site, $_quoteFromRepository);
            $text = $this->computeQuoteSummaryHtml($text, $_quoteFromRepository);
            $text = $this->computeQuoteSummary($text, $_quoteFromRepository);

            //The implemented behavior is different from the original one, probably bugfix
            $text = $this->computeQuoteDestinationName($text, $destinationOfQuote);
        }


        /*
         * USER
         * [user:*]
         */
        $_user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        if($_user) {
            (strpos($text, '[user:first_name]') !== false) and $text = str_replace('[user:first_name]'       , ucfirst(mb_strtolower($_user->firstname)), $text);
        }

        return $text;
    }

    /**
     * @param $text
     * @param Destination $destinationOfQuote
     * @param Site $site
     * @param Quote $_quoteFromRepository
     * @return mixed
     */
    private function computeQuoteDestinationLink($text, Destination $destinationOfQuote, Site $site, Quote $_quoteFromRepository)
    {
        if (strpos($text, '[quote:destination_link]') !== false) {
            $destionationLink = $site->url . '/' . $destinationOfQuote->countryName . '/quote/' . $_quoteFromRepository->id;
            $text = str_replace('[quote:destination_link]', $destionationLink, $text);
        }

        return $text;
    }

    /**
     * @param $text
     * @param Quote $_quoteFromRepository
     * @return mixed
     */
    private function computeQuoteSummaryHtml($text, Quote $_quoteFromRepository)
    {
        $containsSummaryHtml = strpos($text, '[quote:summary_html]');
        if ($containsSummaryHtml !== false) {
            $text = str_replace(
                '[quote:summary_html]',
                Quote::renderHtml($_quoteFromRepository),
                $text
            );
        }
        return $text;
    }

    /**
     * @param $text
     * @param Quote $_quoteFromRepository
     * @return mixed
     */
    private function computeQuoteSummary($text, Quote $_quoteFromRepository)
    {
        $containsSummary = strpos($text, '[quote:summary]');
        if ($containsSummary !== false) {
            $text = str_replace(
                '[quote:summary]',
                Quote::renderText($_quoteFromRepository),
                $text
            );
        }
        return $text;
    }

    /**
     * @param $text
     * @param Destination $destinationOfQuote
     * @return mixed
     */
    private function computeQuoteDestinationName($text, Destination $destinationOfQuote)
    {
        if (strpos($text, '[quote:destination_name]') !== false) {
            $text = str_replace('[quote:destination_name]', $destinationOfQuote->countryName, $text);
        }
        return $text;
    }
}
