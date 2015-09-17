#include <QDebug>

#include "BodyDashboard.h"

BodyDashboard::BodyDashboard(QWidget *parent) : QWidget(parent)
{
    _MainLayoutLoaded = new QVBoxLayout();
    _MemberAvaible = new QHBoxLayout();
    _NextMeeting = new QHBoxLayout();
    _GlobalProgress = new QHBoxLayout();
    _TitleMemberAvaible = new QLabel("Your team occupation");
    _TitleNextMeeting = new QLabel("Your next meetings");
    _TitleGlobalProgress = new QLabel("Global progress");
    _MainLayoutLoaded->addWidget(_TitleMemberAvaible);
    _MainLayoutLoaded->addLayout(_MemberAvaible);
    _MainLayoutLoaded->addWidget(_TitleNextMeeting);
    _MainLayoutLoaded->addLayout(_NextMeeting);
    _MainLayoutLoaded->addWidget(_TitleGlobalProgress);
    _MainLayoutLoaded->addLayout(_GlobalProgress);

    _MemberAvaible->addWidget(new DashboardMember());
    _MemberAvaible->addWidget(new DashboardMember());
    _MemberAvaible->addWidget(new DashboardMember());
    _MemberAvaible->addWidget(new DashboardMember());
    _NextMeeting->addWidget(new DashboardMeeting());
    _NextMeeting->addWidget(new DashboardMeeting());
    _NextMeeting->addWidget(new DashboardMeeting());
    _GlobalProgress->addWidget(new DashboardGlobalProgress());

    setLayout(_MainLayoutLoaded);

    setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);
}

void BodyDashboard::Show(int ID, MainWindow *mainApp)
{
    _ProjectId = ID;
    _MainApplication = mainApp;
    show();
}

void BodyDashboard::Hide()
{
    hide();
}
