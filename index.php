<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire avec Réponse Vocale</title>
    <script src="https://code.responsivevoice.org/responsivevoice.js?key=ADD YOUR KEY"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bulma/0.9.3/css/bulma.min.css">
    <style>
        .loader {
            border: 16px solid #f3f3f3;
            border-top: 16px solid #3498db;
            border-radius: 50%;
            width: 120px;
            height: 120px;
            animation: spin 2s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .button.is-loading::after {
            content: '';
            display: inline-block;
            width: 1em;
            height: 1em;
            border: 2px solid currentColor;
            border-right-color: transparent;
            border-radius: 50%;
            animation: button-spin 0.75s linear infinite;
        }

        @keyframes button-spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <?php
    define('DEFAULT_MISTRAL_API_KEY', 'ADD YOUR KEY');
    define('DEFAULT_MISTRAL_ENDPOINT', 'https://api.mistral.ai/v1/chat/completions');
    define('DEFAULT_MISTRAL_MODEL', 'pixtral-12b-2409');

    $response = '';
    $mavariable = '';
    $loading = false;

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $loading = true;
        $question = $_POST['question'];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . DEFAULT_MISTRAL_API_KEY
        ];

        $data = [
            'model' => DEFAULT_MISTRAL_MODEL,
            'messages' => [['role' => 'user', 'content' => $question]]
        ];

        $ch = curl_init(DEFAULT_MISTRAL_ENDPOINT);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);
        if (isset($responseData['choices'][0]['message']['content'])) {
            $mavariable = $responseData['choices'][0]['message']['content'];
            $loading = false;
        }
    }

    // Échappez les caractères spéciaux pour une utilisation en JavaScript
    $textePourJS = json_encode($mavariable);
    ?>

    <section class="section">
        <div class="container">
            <h1 class="title">Formulaire avec Réponse Vocale</h1>
            <form method="POST" action="">
                <div class="field">
                    <label class="label" for="question">Posez votre question :</label>
                    <div class="control">
                        <input type="text" id="question" name="question" class="input" required>
                    </div>
                </div>
                <div class="control">
                    <button type="submit" class="button is-primary">Valider</button>
                </div>
            </form>

            <?php if ($loading): ?>
                <div class="loader"></div>
            <?php endif; ?>

            <?php if (!empty($mavariable)): ?>
                <div class="box">
                    <p>Réponse : <?php echo htmlspecialchars($mavariable); ?></p>
                    <div class="buttons">
                        <button class="button is-info" id="readMale" onclick="lireTexte('French Male')">Lire (Homme)</button>
                        <button class="button is-danger" id="readFemale" onclick="lireTexte('French Female')">Lire (Femme)</button>
                        <button class="button is-warning" id="readAlternate" onclick="lireTexteAlternate()">Lire (Alterné)</button>
                    </div>
                </div>
                <script>
                    var texte = <?php echo $textePourJS; ?>;

                    function lireTexte(voice) {
                        document.getElementById('readMale').classList.add('is-loading');
                        document.getElementById('readFemale').classList.add('is-loading');
                        document.getElementById('readAlternate').classList.add('is-loading');
                        responsiveVoice.speak(texte, voice, {
                            onend: function() {
                                document.getElementById('readMale').classList.remove('is-loading');
                                document.getElementById('readFemale').classList.remove('is-loading');
                                document.getElementById('readAlternate').classList.remove('is-loading');
                            }
                        });
                    }

                    function lireTexteAlternate() {
                        document.getElementById('readMale').classList.add('is-loading');
                        document.getElementById('readFemale').classList.add('is-loading');
                        document.getElementById('readAlternate').classList.add('is-loading');
                        var sentences = texte.split(/(?<=[.!?])\s+/); // Divise le texte en phrases en utilisant les points, points d'exclamation et points d'interrogation comme séparateurs
                        var index = 0;

                        function readNextSentence() {
                            if (index < sentences.length) {
                                var sentence = sentences[index];
                                var voice = index % 2 === 0 ? "French Male" : "French Female";
                                responsiveVoice.speak(sentence, voice, {
                                    onend: function() {
                                        index++;
                                        readNextSentence();
                                    }
                                });
                            } else {
                                document.getElementById('readMale').classList.remove('is-loading');
                                document.getElementById('readFemale').classList.remove('is-loading');
                                document.getElementById('readAlternate').classList.remove('is-loading');
                            }
                        }

                        readNextSentence();
                    }
                </script>
            <?php endif; ?>

           <div class="content">
    <h2>Tutoriel : Écouter les réponses</h2>
    <p>Vous pouvez écouter les réponses générées par l'IA en utilisant les boutons de lecture. Voici comment procéder :</p>
    <ol>
        <li>Posez votre question dans le champ prévu à cet effet et cliquez sur "Valider".</li>
        <li>Une fois la réponse affichée, vous verrez trois boutons de lecture :</li>
        <ul>
            <li><strong>Lire (Homme)</strong> : Cliquez sur ce bouton pour écouter la réponse avec une voix masculine.</li>
            <li><strong>Lire (Femme)</strong> : Cliquez sur ce bouton pour écouter la réponse avec une voix féminine.</li>
            <li><strong>Lire (Alterné)</strong> : Cliquez sur ce bouton pour écouter la réponse avec une alternance entre une voix masculine et une voix féminine à chaque phrase.</li>
        </ul>
        <li>Cliquez sur le bouton de votre choix pour écouter la réponse.</li>
    </ol>
</div>

        </div>
    </section>
</body>
</html>
