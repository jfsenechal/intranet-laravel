<html lang="en">
<head>
    <title>Déclaration de déplacement</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .page-breaker {
            page-break-after: always;
        }
    </style>
</head>
<body>
<div class="container">

    {% if user %}
        {{ user.nom | upper }} {{ user.prenom }}
    {% else %}
        {{ username }}
    {% endif %}

    <br/>
    {% if profile %}
        {{ profile.rue }}
        <br/>
        {{ profile.codepostal }} {{ profile.localite }}<br/>
        <strong>Numéro de compte :</strong> {{ profile.iban }}
        <br/>
        <strong>Omnium :</strong>
        {% if profile.omnium %}
            <span class="fas fa-check"></span> {% else %} Non {% endif %}
        <br/>
        <strong>Délibé Collège :</strong>
        {% if profile.deplacementDateCollege %}
            {{ profile.deplacementDateCollege | date('d-m-Y') }}
        {% endif %}
        <br/>
    {% endif %}
    <br/>
    <table class="table table-bordered table-striped">
        <thead>
        <tr>
            <th>Année</th>
            <th>Type</th>
            {% for month in months %}
                <th>{{ month }}</th>
            {% endfor %}
            <th>Total</th>
        </tr>
        </thead>
        <tbody>

        {% set internes = deplacements.interne %}
        {% set externes = deplacements.externe %}

        {% for year in years %}
            {% set totalInterne = 0 %}
            {% set totalExterne = 0 %}
            <tr>
                <td>{{ year }}</td>
                <td>Interne</td>
                {% for nummonth in months|keys %}
                    <td>
                        {% if internes[year][nummonth] is defined %}
                            {{ internes[year][nummonth] }}
                            {% set totalInterne = totalInterne + internes[year][nummonth] %}
                        {% endif %}
                    </td>
                {% endfor %}
                <td>{{ totalInterne }}</td>
            </tr>
            <tr>
                <td></td>
                <td>Externe</td>
                {% for nummonth in months|keys %}
                    <td>
                        {% if externes[year][nummonth] is defined %}
                            {{ externes[year][nummonth] }}
                            {% set totalExterne = totalExterne + externes[year][nummonth] %}
                        {% endif %}
                    </td>
                {% endfor %}
                <td>{{ totalExterne }}</td>
            </tr>
        {% endfor %}
        </tbody>
    </table>
    <div class="page-breaker"></div>
</div>
</body>
</html>
