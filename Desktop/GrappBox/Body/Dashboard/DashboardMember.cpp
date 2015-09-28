#include "DashboardMember.h"
#include "SFontLoader.h"

#include <QFontDatabase>
#include <QBitmap>

DashboardMember::DashboardMember(DashboardInformation::MemberAvaiableInfo *info, QWidget *parent, int userId) : QWidget(parent)
{
    setFixedSize(160, 200);
    setMinimumSize(160, 200);
    setMaximumSize(160, 200);
    _UserId = userId;
    _MemberPictureDrawer = new QLabel();


    _MemberName = new QLabel(info->MemberName);
    _MemberName->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);
    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(12);
    font.setBold(true);
    _MemberName->setFont(font);
    _MemberName->setStyleSheet("QLabel { color: #ffffff; }");
    _BusyDrawer = new QLabel((info->IsBusy) ? "Busy" : "Free");
    _BusyDrawer->setFont(font);
    _BusyDrawer->setStyleSheet(QString("QLabel { border-bottom-right-radius: 10px; border-bottom-left-radius: 10px; background: ") + QString((info->IsBusy) ? "#af2d2e" : "#0ab239") + QString("; color: #ffffff; }"));
    _BusyDrawer->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);
    _BusyDrawer->setFixedSize(160, 40);
    if (!info->IsBusy)
    {
        _AddTaskButton = new QPushButton("+");
        _AddTaskButton->setFixedSize(40, 40);
        QFont buttonFont(font);
        buttonFont.setPointSize(24);
        _AddTaskButton->setFont(buttonFont);
        _AddTaskButton->setStyleSheet("QPushButton {"
                                      "background: #0ab239;"
                                      "color: #ffffff;"
                                      "border: 0px;"
                                      "border-bottom-right-radius: 10px;"
                                      "}"
                                      "QPushButton:hover {"
                                      "color: #232323;"
                                      "}"
                                      "QPushButton:pressed {"
                                      "color: #505050;"
                                      "}");
    }
    _MainLayout = new QVBoxLayout();
    _StateLayout = new QGridLayout();
    _StateLayout->setSpacing(0);
    _StateLayout->setMargin(0);



    QPixmap tmpPixmap = info->MemberPicture->scaled(130, 130);
    tmpPixmap.setMask(QBitmap::fromImage(QImage(":/Mask/Ressources/Mask/CircleMaskMemberPicture.png").createAlphaMask(Qt::MonoOnly)));
    _MemberPictureDrawer->setPixmap(tmpPixmap);


    _MemberPictureDrawer->setAlignment(Qt::AlignHCenter | Qt::AlignBottom);


    _StateLayout->addWidget(_BusyDrawer, 0, 0, 1, 1);
    if (!info->IsBusy)
        _StateLayout->addWidget(_AddTaskButton, 0, 0, 1, 1, Qt::AlignRight | Qt::AlignVCenter);
    _StateLayout->setMargin(0);
    _StateLayout->setSpacing(0);

    _MainLayout->setSpacing(0);
    _MainLayout->setMargin(0);
    _MainLayout->addWidget(_MemberPictureDrawer, 110);
    _MainLayout->addWidget(_MemberName, 30);
    _MainLayout->addLayout(_StateLayout, 20);
    this->setLayout(_MainLayout);

    this->setObjectName("DashboardMember");
    this->setStyleSheet("DashboardMember {background: #2d2f31;"
                        "border-radius: 10px;"
                        "border: none;}");
}

void DashboardMember::paintEvent(QPaintEvent *)
 {
     QStyleOption opt;
     opt.init(this);
     QPainter p(this);
     style()->drawPrimitive(QStyle::PE_Widget, &opt, &p, this);
 }
