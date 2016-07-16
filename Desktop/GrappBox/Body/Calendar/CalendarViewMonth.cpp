#include <QVariant>
#include <QDebug>
#include "CalendarViewMonth.h"

CalendarViewMonth::CalendarViewMonth()
{
    _MainLayout = new QGridLayout();

    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayout->setSpacing(0);

    QDate dateTmp(2016, 01, 04);

    for (int i = 0; i < 7; ++i)
    {
        QLabel *day = new QLabel(dateTmp.toString("dddd"));
        day->setFixedHeight(30);
        day->setAlignment(Qt::AlignCenter);
        _MainLayout->addWidget(day, 0, i, 1, 1);
        dateTmp = dateTmp.addDays(1);
    }

    setLayout(_MainLayout);
    _AssociatedDate = QDate(2016, 03, 01);

    LoadWidgets();
}

void CalendarViewMonth::LoadEvents(QList<Event *> events, QDate date)
{
    CalendarView::LoadEvents(events, date);
    _AssociatedDate.setDate(date.year(), date.month(), 1);
    if (_Days.size() == 0)
        LoadWidgets();
    else
        ReloadWidgets();
}

void CalendarViewMonth::LoadWidgets()
{
    QDate creationDate = _AssociatedDate;
    if (creationDate.dayOfWeek() != 1)
        creationDate = creationDate.addDays(-(creationDate.dayOfWeek() - 1));
    for (int i = 0; i < 35; ++i)
    {
        CalendarEventMonth *newLabel = new CalendarEventMonth(creationDate);

        if (creationDate.month() != _AssociatedDate.month())
            newLabel->setDisabled(true);
        _MainLayout->addWidget(newLabel, i / 7 + 1, i % 7, 1, 1);
        _Days.push_back(newLabel);
        newLabel->LoadEvents(_Events, creationDate);
        creationDate = creationDate.addDays(1);
    }
}

void CalendarViewMonth::ReloadWidgets()
{
    QDate creationDate = _AssociatedDate;
    if (creationDate.dayOfWeek() != 1)
        creationDate = creationDate.addDays(-(creationDate.dayOfWeek() - 1));
    for (int i = 0; i < 35; ++i)
    {
        CalendarEventMonth *newLabel = _Days.at(i);
        if (creationDate.month() != _AssociatedDate.month())
            newLabel->setDisabled(true);
        else
            newLabel->setDisabled(false);
        _Days.push_back(newLabel);
        newLabel->LoadEvents(_Events, creationDate);
        creationDate = creationDate.addDays(1);
    }
}

void CalendarViewMonth::HideProject(int id)
{
    for (CalendarEventMonth *event : _Days)
        event->HideProject(id);
}

void CalendarViewMonth::ShowProject(int id)
{
    for (CalendarEventMonth *event : _Days)
        event->ShowProject(id);
}
