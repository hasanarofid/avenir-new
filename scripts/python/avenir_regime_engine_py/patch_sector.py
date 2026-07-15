import re

with open("sector_rotation_new.py") as f:
    code = f.read()

# Replace config
config_pattern = r'SECTOR_CSV_PATH = .*?\n\nOUTPUT_DIR = .*?OUTPUT_LATEST_JSON = .*?\.json"'
new_config = """SECTOR_CSV_PATH = None
OUTPUT_DIR = None
OUTPUT_FEATURES_CSV = None
OUTPUT_LATEST_JSON = None"""
code = re.sub(config_pattern, new_config, code, flags=re.DOTALL)

# Replace pipeline function
pipeline_pattern = r'def run_pipeline\(\):.*?return latest_payload, feature_df'
new_pipeline = """def run_pipeline(input_file, output_dir):
    out = Path(output_dir)
    out.mkdir(parents=True, exist_ok=True)
    
    df = load_sector_file(Path(input_file))
    feature_df = add_sector_rotation_features(df)
    feature_df.to_csv(out / "sector_rotation_features.csv", index=False)
    
    latest_payload = build_latest_payload(feature_df)
    
    with open(out / "latest_sector_rotation_score.json", "w", encoding="utf-8") as f:
        json.dump(latest_payload, f, indent=2, ensure_ascii=False)
        
    return latest_payload, feature_df"""
code = re.sub(pipeline_pattern, new_pipeline, code, flags=re.DOTALL)

# Replace main
main_pattern = r'if __name__ == "__main__":.*?latest_payload, feature_df = run_pipeline\(\)'
new_main = """if __name__ == "__main__":
    import argparse
    parser = argparse.ArgumentParser()
    parser.add_argument('--input', required=True)
    parser.add_argument('--output-dir', required=True)
    # ignore other old arguments that breadthservice might send
    parser.add_argument('--stocks', required=False)
    parser.add_argument('--sector-master', required=False)
    parser.add_argument('--stock-sheet-name', required=False)
    parser.add_argument('--sector-sheet-name', required=False)
    args = parser.parse_args()
    
    latest_payload, feature_df = run_pipeline(args.input, args.output_dir)"""
code = re.sub(main_pattern, new_main, code, flags=re.DOTALL)

with open("sector_rotation.py", "w") as f:
    f.write(code)

print("Patched!")
