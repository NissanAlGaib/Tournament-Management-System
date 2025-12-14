#!/bin/bash
# Tournament Database Schema Validator
# This script validates the SQL syntax and provides a summary

echo "=========================================="
echo "Tournament Management Schema Validator"
echo "=========================================="
echo ""

SQL_FILE="tournament_management.sql"

if [ ! -f "$SQL_FILE" ]; then
    echo "❌ Error: $SQL_FILE not found!"
    exit 1
fi

echo "✓ SQL file found: $SQL_FILE"
echo ""

# Count different database objects
echo "📊 Database Objects Summary:"
echo "----------------------------"

# Count tables
TABLE_COUNT=$(grep -c "^CREATE TABLE" "$SQL_FILE")
echo "Tables:             $TABLE_COUNT"

# Count views
VIEW_COUNT=$(grep -c "^CREATE.*VIEW" "$SQL_FILE")
echo "Views:              $VIEW_COUNT"

# Count stored procedures
PROC_COUNT=$(grep -c "^CREATE PROCEDURE" "$SQL_FILE")
echo "Stored Procedures:  $PROC_COUNT"

# Count triggers
TRIGGER_COUNT=$(grep -c "^CREATE TRIGGER" "$SQL_FILE")
echo "Triggers:           $TRIGGER_COUNT"

# Count indexes
INDEX_COUNT=$(grep -c "^CREATE INDEX" "$SQL_FILE")
echo "Indexes:            $INDEX_COUNT"

echo ""
echo "📋 Table List:"
echo "----------------------------"
grep "^CREATE TABLE" "$SQL_FILE" | sed 's/CREATE TABLE IF NOT EXISTS `//g' | sed 's/` (//g' | while read -r table; do
    echo "  • $table"
done

echo ""
echo "👁️  View List:"
echo "----------------------------"
grep "^CREATE.*VIEW" "$SQL_FILE" | sed 's/CREATE OR REPLACE VIEW //g' | sed 's/ AS//g' | while read -r view; do
    echo "  • $view"
done

echo ""
echo "🔧 Stored Procedures:"
echo "----------------------------"
grep "^CREATE PROCEDURE" "$SQL_FILE" | sed 's/CREATE PROCEDURE //g' | sed 's/(.*//g' | while read -r proc; do
    echo "  • $proc"
done

echo ""
echo "⚡ Triggers:"
echo "----------------------------"
grep "^CREATE TRIGGER" "$SQL_FILE" | sed 's/CREATE TRIGGER //g' | while read -r trigger; do
    echo "  • $trigger"
done

echo ""
echo "📏 File Statistics:"
echo "----------------------------"
LINES=$(wc -l < "$SQL_FILE")
SIZE=$(du -h "$SQL_FILE" | cut -f1)
echo "Lines:              $LINES"
echo "Size:               $SIZE"

echo ""
echo "✨ Feature Coverage:"
echo "----------------------------"

# Check for key features from requirements
features=(
    "Tournament configuration.*format"
    "Registration.*deadline"
    "Match.*tracking"
    "Prize.*pool"
    "Tournament.*status"
    "registration_requirements"
)

feature_names=(
    "Tournament configuration (rules, format, size)"
    "Registration deadline management"
    "Match result tracking"
    "Prize pool and rewards"
    "Tournament status management"
    "Registration requirements setup"
)

for i in "${!features[@]}"; do
    if grep -qi "${features[$i]}" "$SQL_FILE"; then
        echo "  ✓ ${feature_names[$i]}"
    else
        echo "  ✗ ${feature_names[$i]}"
    fi
done

echo ""
echo "🔍 Validation Checks:"
echo "----------------------------"

# Check for proper delimiter handling
DELIMITER_START=$(grep -c "^DELIMITER //" "$SQL_FILE")
DELIMITER_END=$(grep -c "^DELIMITER ;" "$SQL_FILE")
if [ "$DELIMITER_START" -eq "$DELIMITER_END" ]; then
    echo "  ✓ DELIMITER statements balanced"
else
    echo "  ⚠ DELIMITER statements unbalanced ($DELIMITER_START start vs $DELIMITER_END end)"
fi

# Check for foreign key constraints
FK_COUNT=$(grep -c "CONSTRAINT.*FOREIGN KEY" "$SQL_FILE")
echo "  ✓ Foreign key constraints: $FK_COUNT"

# Check for indexes
if [ "$INDEX_COUNT" -gt 0 ]; then
    echo "  ✓ Performance indexes defined"
else
    echo "  ℹ No explicit indexes (table keys only)"
fi

# Check for proper table engine
ENGINE_CHECK=$(grep -c "ENGINE=InnoDB" "$SQL_FILE")
if [ "$ENGINE_CHECK" -eq "$TABLE_COUNT" ]; then
    echo "  ✓ All tables use InnoDB engine"
else
    echo "  ⚠ Some tables may not use InnoDB"
fi

# Check for charset
CHARSET_CHECK=$(grep -c "CHARSET=utf8mb4" "$SQL_FILE")
if [ "$CHARSET_CHECK" -gt 0 ]; then
    echo "  ✓ UTF8MB4 charset specified"
else
    echo "  ℹ No explicit charset specified"
fi

echo ""
echo "=========================================="
echo "✅ Validation Complete!"
echo "=========================================="
echo ""
echo "Next Steps:"
echo "1. Review the summary above"
echo "2. Import the SQL file: mysql -u root -p tournament_db < $SQL_FILE"
echo "3. Verify tables created: SHOW TABLES LIKE 'tournament%';"
echo "4. Check the TOURNAMENT_SETUP_README.md for integration guide"
echo ""
