# The connection color strings may have length < 6 due to bug with inserting data.
# This fixes any incorrect values that currently exist in the database.
UPDATE connections SET color = LPAD(color,6,"0") WHERE LENGTH(color) != 6;