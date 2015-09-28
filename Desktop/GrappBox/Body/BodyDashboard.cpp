#include <QDebug>
#include <QScrollArea>

#include "SFontLoader.h"
#include "Dashboard/DashboardInformation.h"
#include "BodyDashboard.h"

using namespace DashboardInformation;

BodyDashboard::BodyDashboard(QWidget *parent) : QWidget(parent)
{
    _MainLayoutLoaded = new QVBoxLayout();
    _MemberAvaible = new QHBoxLayout();
    _NextMeeting = new QHBoxLayout();
    _GlobalProgress = new QHBoxLayout();

    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Leo Nadeau", false, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/leo_nadeau.jpg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Allyriane Launois", true, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/allyriane_launois.jpg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Frédéric Tan", true, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/frederic_tan.jpg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Marc Wieser", false, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/marc_wieser.jpeg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Pierre Feytout", false, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/pierre_feytout.jpg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Pierre Hofman", true, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/pierre_hofman.jpg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Roland Hemmer", false, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/roland_hemmer.jpg"))));
    _MemberAvaible->addWidget(new DashboardMember(new MemberAvaiableInfo("Valentin Mougenot", true, new QPixmap(":/Temporary/Profils/Ressources/Temporary/Profils/valentin_mougenot.jpg"))));

    _NextMeeting->addWidget(new DashboardMeeting(new NextMeetingInfo(NextMeetingInfo::Client, "Folow up", "25/09/2015", "20h30", new QPixmap(":/Temporary/Project/Ressources/Temporary/Projects/GameSphere.png")) ));
    _NextMeeting->addWidget(new DashboardMeeting(new NextMeetingInfo(NextMeetingInfo::Company, "UML Conception", "30/10/2015", "16h30", new QPixmap(":/Temporary/Project/Ressources/Temporary/Projects/GameSphere.png")) ));
    _NextMeeting->addWidget(new DashboardMeeting(new NextMeetingInfo(NextMeetingInfo::Company, "Leader Meeting", "5/11/2015", "10h20", new QPixmap(":/Temporary/Project/Ressources/Temporary/Projects/GameSphere.png")) ));
    _NextMeeting->addWidget(new DashboardMeeting(new NextMeetingInfo(NextMeetingInfo::Personnal, "Happy new year", "31/12/2015", "23h59", new QPixmap(":/Temporary/Project/Ressources/Temporary/Projects/GameSphere.png")) ));
    _NextMeeting->addWidget(new DashboardMeeting(new NextMeetingInfo(NextMeetingInfo::Personnal, "Vacation End", "5/01/2016", "9h00", new QPixmap(":/Temporary/Project/Ressources/Temporary/Projects/GameSphere.png")) ));

    _GlobalProgress->addWidget(new DashboardGlobalProgress(new GlobalProgressInfo("Grappbox Project", "Grappbox Co", "0102030405", "mail@grappbox.com", 42, 3, 8, 2, new QPixmap(":/Temporary/Project/Ressources/Temporary/Projects/GameSphere.png"))));

    QFont font;
    font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(20);
    QString titleStyleSheet = "QLabel { color: #af2d2e;}";
    _TitleMemberAvaible = new QLabel("Your team occupation");
    _TitleMemberAvaible->setFont(font);
    _TitleMemberAvaible->setStyleSheet(titleStyleSheet);
    _TitleNextMeeting = new QLabel("Your next meetings");
    _TitleNextMeeting->setFont(font);
    _TitleNextMeeting->setStyleSheet(titleStyleSheet);
    _TitleGlobalProgress = new QLabel("Global progress");
    _TitleGlobalProgress->setFont(font);
    _TitleGlobalProgress->setStyleSheet(titleStyleSheet);
    QWidget *TopWidget = new QWidget();
    QVBoxLayout *TopLayout = new QVBoxLayout();
    QWidget *MemberWidget = new QWidget();
    MemberWidget->setObjectName("MemberWidget");
    MemberWidget->setStyleSheet("QWidget#MemberWidget { background: transparent;}");
    MemberWidget->setLayout(_MemberAvaible);
    QScrollArea *scrollMember = new QScrollArea();
    scrollMember->setWidget(MemberWidget);
    scrollMember->setFixedHeight(_MemberAvaible->geometry().height() + 20);
    scrollMember->setStyleSheet("QScrollArea {border: none; background: transparent;}");

    TopLayout->addWidget(_TitleMemberAvaible);
    TopLayout->addWidget(scrollMember);
    TopWidget->setLayout(TopLayout);

    QWidget *MiddleWidget = new QWidget();
    MiddleWidget->setObjectName("MiddleWidget");
    MiddleWidget->setStyleSheet("QWidget#MiddleWidget { background: #f0f0f0; }");
    QVBoxLayout *MiddleLayout = new QVBoxLayout();
    QWidget *NextMeetingWidget = new QWidget();
    NextMeetingWidget->setObjectName("MemberWidget");
    NextMeetingWidget->setStyleSheet("QWidget#MemberWidget { background: transparent;}");
    NextMeetingWidget->setLayout(_NextMeeting);
    QScrollArea *scrollMeeting = new QScrollArea();
    scrollMeeting->setWidget(NextMeetingWidget);
    scrollMeeting->setFixedHeight(_NextMeeting->geometry().height() + 20);
    scrollMeeting->setStyleSheet("QScrollArea {border: none; background: transparent;}");
    MiddleLayout->addWidget(_TitleNextMeeting);
    MiddleLayout->addWidget(scrollMeeting);
    MiddleWidget->setLayout(MiddleLayout);

    QWidget *BottomWidget = new QWidget();
    QVBoxLayout *BottomLayout = new QVBoxLayout();
    QWidget *GlobalProgressWidget = new QWidget();
    GlobalProgressWidget->setObjectName("MemberWidget");
    GlobalProgressWidget->setStyleSheet("QWidget#MemberWidget { background: transparent;}");
    GlobalProgressWidget->setLayout(_GlobalProgress);
    QScrollArea *scrollProgress = new QScrollArea();
    scrollProgress->setWidget(GlobalProgressWidget);
    scrollProgress->setFixedHeight(_GlobalProgress->geometry().height() + 20);
    scrollProgress->setStyleSheet("QScrollArea {border: none; background: transparent;}");
    BottomLayout->addWidget(_TitleGlobalProgress);
    BottomLayout->addWidget(scrollProgress);
    BottomWidget->setLayout(BottomLayout);

    _MainLayoutLoaded->addWidget(TopWidget, 5);
    _MainLayoutLoaded->addWidget(MiddleWidget, 5);
    _MainLayoutLoaded->addWidget(BottomWidget, 5);

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
