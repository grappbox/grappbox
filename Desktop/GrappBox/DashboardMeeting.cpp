#include <QDebug>

#include "DashboardMeeting.h"

DashboardMeeting::DashboardMeeting(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    _HeadLayout = new QHBoxLayout();
    _FooterLayout = new QHBoxLayout();

    _ProjectIcon = new QLabel("Project icon");
    _MeetingName = new QLabel("Rendez-vous");
    _TypeIcon = new QLabel("Mon icon type");

    _CalendarIcon = new QLabel();
    _Date = new QLabel("31/12/1994");
    _HourIcon = new QLabel();
    _Hours = new QLabel("13:31 GMT+0");

    _HeadLayout->addWidget(_ProjectIcon);
    _HeadLayout->addWidget(_MeetingName);

    QWidget *FooterWidget = new QWidget();
    _FooterLayout->addWidget(_CalendarIcon);
    _FooterLayout->addWidget(_Date);
    _FooterLayout->addWidget(_HourIcon);
    _FooterLayout->addWidget(_Hours);
    FooterWidget->setLayout(_FooterLayout);
    //FooterWidget->setStyleSheet("background: #af2d2e;");

    _MainLayout->addLayout(_HeadLayout, 59);
    _MainLayout->addWidget(_TypeIcon, 96);
    qDebug() << "Debug meeting !";
    _MainLayout->addWidget(FooterWidget, 51);
    _MainLayout->setMargin(0);
    _MainLayout->setSpacing(0);

    setLayout(_MainLayout);
    this->setStyleSheet("background: #2d2f31;");
    setMaximumSize(212, 206);
    setMinimumSize(maximumSize());
}
