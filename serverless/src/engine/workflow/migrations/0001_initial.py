# -*- coding: utf-8 -*-
# Generated by Django 1.11.18 on 2019-01-29 03:31
from __future__ import unicode_literals

from django.db import migrations, models


class Migration(migrations.Migration):

    initial = True

    dependencies = [
    ]

    operations = [
        migrations.CreateModel(
            name='Process',
            fields=[
                ('id', models.UUIDField(editable=False, primary_key=True, serialize=False)),
                ('name', models.CharField(max_length=100)),
                ('date', models.DateTimeField()),
                ('state', models.IntegerField()),
            ],
        ),
    ]
