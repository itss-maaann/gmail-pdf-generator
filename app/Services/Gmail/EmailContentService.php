<?php

namespace App\Services\Gmail;

class EmailContentService
{
    public function extractAllConversations(): array
    {
        $conversations = [];

        for ($i = 1; $i <= 35; $i++) {

            $message = str_repeat($this->getText('message'), 200);
            $reply =   str_repeat($this->getText('reply'), 200);

            $conversations[$i] = [$message, $reply];
        }

        return $conversations;
    }

    protected function getText(string $type): string
    {
        return match ($type) {
            'message' => "Surface attention attack technology. Walk now Surface attention attack technology. Walk now often always. Information on mission various. Prove fire enter
            capital population. First sell authority leader your you available. Media car give attention each. Citizen street region
            particularly would. Account stage federal professional voice care break. Score choice example decision. Data plant enough
            major town suffer. Plant stop analysis four. Pm energy scientist necessary. Night born war real chance along. Old challenge
            camera final together someone. Together decide economic. Government nice themselves wind. Understand door class son.
            Score each cause. Quality throughout beautiful instead. Behavior discussion own. Current practice nation determine
            operation speak according. Recently future choice whatever. Bill here grow gas enough analysis. Movie win her need stop peace
            technology. Court attorney product significant world talk term. Everyone player half have decide environment. Participant commercial rock clear.
            Establish understand read detail food shoulder. Director allow firm environment. Tree note responsibility defense material. Central cause seat much
            section investment on. Despite young meeting before another body. Civil quite others his other life edge network. Quite boy those. Shoulder future fall
            citizen about. Will seven medical blood personal. Participant check several much single morning a. Major born guy world southern dream.
            There water beat magazine attorney. She campaign little near enter their institution. Up sense ready require human. Just military building different full open.
            Open according remain arrive attack. Teacher audience draw. Democrat car very number line six space. Behind probably great in tell. Pull worker better.
            Rock song body court movie cell. Everything economic type kitchen. Better present music address behavior send door. Magazine degree husband around her world.
            Unit size expect recent room. Product main couple design around save article. Arm once me system church whether. <br>",

            'reply'   => "Thing agent say forward. Environment skin blue the teach. Current truth glass star the. Age cover foreign ten whom.
            Go meeting quickly such former. Boy wife condition. Although others generation skill job.Score each cause. Quality throughout beautiful instead.
            Behavior discussion own. Current practice nation determine operation speak according. Recently future choice whatever. Bill here grow gas enough analysis.
            Movie win her need stop peace technology. Court attorney product significant world talk term. Everyone player half have decide environment.
            Participant commercial rock clear. Establish understand read detail food shoulder.
            Director allow firm environment. Tree note responsibility defense material. Central cause seat much section investment on.
            Despite young meeting before another body.
            Civil quite others his other life edge network. Quite boy those. Shoulder future fall citizen about. Will seven medical blood personal. Participant check several much
            single morning a. Major born guy world southern dream. There water beat magazine attorney. She campaign little near enter their institution. Up sense ready require human.
            Just military building different full open. Open according remain arrive attack. Teacher audience draw. Democrat car very number line six space. Behind probably great in tell.
            Pull worker better. Rock song body court movie cell. Everything economic type kitchen. Better present music address behavior send door. Magazine degree husband around her world.
            Unit size expect recent room. Product main couple design around save article. Arm once me system church whether. <br>",

            default   => '',
        };
    }
}
