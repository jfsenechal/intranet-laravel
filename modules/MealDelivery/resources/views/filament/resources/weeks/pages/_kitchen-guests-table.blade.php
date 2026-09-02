@if ($guests['total'] > 0)
    <h4><strong>Repas invités</strong> — aucun régime pour ces menus</h4>

    <div style="max-width: 340px;">
        <table class="menu-card">
            <tbody>
                <tr>
                    <th>Invités</th>
                    <th>Nombre</th>
                </tr>
                <tr>
                    <td>Menu 1</td>
                    <th>{{ $guests['menu1'] }}</th>
                </tr>
                <tr>
                    <td>Menu 2</td>
                    <th>{{ $guests['menu2'] }}</th>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <th>{{ $guests['total'] }}</th>
                </tr>
            </tbody>
        </table>
    </div>
@endif
