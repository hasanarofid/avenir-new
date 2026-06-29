import re

with open('app/Http/Controllers/DeskBriefController.php', 'r') as f:
    content = f.read()

# Update get() to first() for $latestBrief and with('drivers')
content = content.replace(
    "$latestBrief = DeskBrief::with('marketStance')->orderBy('date', 'desc')->first();",
    "$latestBrief = DeskBrief::with(['marketStance', 'drivers'])->orderBy('date', 'desc')->first();"
)

# Insert the SmartMoney logic for delta
delta_replacement = """        $yesterdaySmart = SmartMoneyFlow::orderBy('date', 'desc')->skip(1)->first();
        $todaySmart = SmartMoneyFlow::orderBy('date', 'desc')->first();
        if ($todaySmart && $yesterdaySmart) {
            $delta['foreign_flow_today'] = $todaySmart->cumulative_vs;
            $delta['flow_flipped'] = ($todaySmart->cumulative_vs !== $yesterdaySmart->cumulative_vs);
        } else {
            $delta['foreign_flow_today'] = 'Net Buy';
            $delta['flow_flipped'] = false;
        }"""

# Insert right after the regime logic
pattern = r"(\$delta\['regime_trend'\] = 'neutral';\n            }\n        })"
content = re.sub(pattern, r"\1\n\n" + delta_replacement, content)

with open('app/Http/Controllers/DeskBriefController.php', 'w') as f:
    f.write(content)
