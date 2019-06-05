# -*- coding: utf-8 -*-
from __future__ import unicode_literals

from django.db import models

# Create your models here.
class Process(models.Model):
	id = models.UUIDField(primary_key=True, editable=False)
	name = models.CharField(max_length=100)
	date = models.DateTimeField()
	state = models.IntegerField()
