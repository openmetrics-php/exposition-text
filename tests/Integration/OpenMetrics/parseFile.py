from prometheus_client.openmetrics.parser import text_string_to_metric_families
from path import path
import sys

metrics = path(sys.argv[1]).bytes()

for family in text_string_to_metric_families(metrics):
  for sample in family.samples:
    print("Name: {0} Labels: {1} Value: {2} Timestamp: {3}".format(*sample))