#include <QDebug>
#include "CalendarEvent.h"

CalendarEvent::CalendarEvent(Event event, QWidget *parent) : QWidget(parent)
{
    _Label = new QLabel(QString("[") + event.Start.time().toString("hh:mm") + "] " + event.Title);

    setStyleSheet("background: #F20000;");

    _MainLayout = new QHBoxLayout();
    _MainLayout->addWidget(_Label);
    _MainLayout->setSpacing(0);
    _MainLayout->setContentsMargins(2, 0, 0, 2);

    setLayout(_MainLayout);
}

