<table style="border:2px solid orange;width:100%;text-align: left;margin-bottom: 10px;">
    <tbody>
    <tr>
        <th colspan="2" style="background: #f9f9f9;border-bottom: 1px solid #000000;">
            Meerkat Debug Information - <span style="font-family: monospace">Meerkat: {{ $version }}
            Statamic: {{ $statamicVersion }}</span></th>
    </tr>
    @foreach($report as $reportItem)
        <tr>
            <th style="width:170px;background: #f9f9f9;border-right: 1px solid #b9b7b7">{{ $reportItem['header'] }}:</th>
            <td><span style="padding:3px;font-family: monospace">{{ $reportItem['value'] }}</span></td>
        </tr>
    @endforeach
    </tbody>
</table>
