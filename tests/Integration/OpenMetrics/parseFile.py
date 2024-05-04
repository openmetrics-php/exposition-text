from prometheus_client.openmetrics.parser import text_string_to_metric_families
import sys

input = sys.stdin.read()

for family in text_string_to_metric_families(input):
  for sample in family.samples:
    print("Name: {0} Labels: {1} Value: {2} Timestamp: {3}".format(*sample))