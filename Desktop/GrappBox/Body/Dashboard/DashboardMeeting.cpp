#include <QDebug>

#include "SFontLoader.h"
#include "DashboardMeeting.h"

DashboardMeeting::DashboardMeeting(DashboardInformation::NextMeetingInfo *info, QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    _HeadLayout = new QHBoxLayout();
    _FooterLayout = new QHBoxLayout();
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(10);
    font.setBold(true);

    _ProjectIcon = new QLabel();
    _ProjectIcon->setFixedSize(40, 40);
    _ProjectIcon->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);
    _ProjectIcon->setPixmap(*info->ProjectIcon);
    _MeetingName = new QLabel(info->MeetingName);
    _MeetingName->setFont(font);
    _MeetingName->setStyleSheet("QLabel { color: #ffffff; }");
    _TypeIcon = new QLabel();
    switch (info->Type)
    {
    case DashboardInformation::NextMeetingInfo::Client:
        _TypeIcon->setPixmap(QPixmap(":/Temporary/MeetingType/Ressources/Temporary/MeetingIcon/ClientMeeting.png"));
        break;
    case DashboardInformation::NextMeetingInfo::Company:
        _TypeIcon->setPixmap(QPixmap(":/Temporary/MeetingType/Ressources/Temporary/MeetingIcon/TeamMeeting.png"));
        break;
    case DashboardInformation::NextMeetingInfo::Personnal:
        _TypeIcon->setPixmap(QPixmap(":/Temporary/MeetingType/Ressources/Temporary/MeetingIcon/Other.png"));
        break;
    };
    _TypeIcon->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);


    _CalendarIcon = new QLabel();
    _CalendarIcon->setPixmap(QPixmap(":/Temporary/MeetingType/Ressources/Temporary/MeetingIcon/Calendar.png"));
    _CalendarIcon->setFixedSize(32, 32);
    _CalendarIcon->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);
    _Date = new QLabel(info->Date);
    _Date->setFont(font);
    _Date->setStyleSheet("QLabel { color: #ffffff; }");
    _HourIcon = new QLabel();
    _HourIcon->setPixmap(QPixmap(":/Temporary/MeetingType/Ressources/Temporary/MeetingIcon/Clock.png"));
    _HourIcon->setFixedSize(32, 32);
    _HourIcon->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);
    _Hours = new QLabel(info->Hours);
    _Hours->setFont(font);
    _Hours->setStyleSheet("QLabel { color: #ffffff; }");

    _HeadLayout->addWidget(_ProjectIcon);
    _HeadLayout->addWidget(_MeetingName);

    QWidget *FooterWidget = new QWidget();
    _FooterLayout->addWidget(_CalendarIcon);
    _FooterLayout->addWidget(_Date);
    _FooterLayout->addWidget(_HourIcon);
    _FooterLayout->addWidget(_Hours);
    FooterWidget->setLayout(_FooterLayout);
    FooterWidget->setStyleSheet("background: #af2d2e;"
                                "border-bottom-left-radius: 10px;"
                                "border-bottom-right-radius: 10px;");

    _HeadLayout->setSpacing(10);
    _MainLayout->addLayout(_HeadLayout, 59);
    _MainLayout->addWidget(_TypeIcon, 115);
    _MainLayout->addWidget(FooterWidget, 51);
    _MainLayout->setMargin(0);
    _MainLayout->setSpacing(0);

    setLayout(_MainLayout);
    this->setObjectName("DashboardMeeting");
    this->setStyleSheet("DashboardMeeting {background: #2d2f31;"
                        "border-radius: 10px;"
                        "border: none;}");
    setMaximumSize(212, 206);
    setMinimumSize(maximumSize());
}

void DashboardMeeting::paintEvent(QPaintEvent *)
 {
     QStyleOption opt;
     opt.init(this);
     QPainter p(this);
     style()->drawPrimitive(QStyle::PE_Widget, &opt, &p, this);
 }
