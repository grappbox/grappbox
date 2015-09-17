#include "DashboardMember.h"

DashboardMember::DashboardMember(QWidget *parent, int userId) : QWidget(parent)
{
    setMaximumSize(150, 200);
    _UserId = userId;
    _MemberPictureDrawer = new QLabel("*Image*");
    _MemberName = new QLabel("Prenom Nom");
    _BusyDrawer = new QLabel("Free");
    _AddTaskButton = new QPushButton("+");

    _MainLayout = new QVBoxLayout();
    _StateLayout = new QHBoxLayout();

    _MainLayout->addSpacing(1);
    _MainLayout->addWidget(_MemberPictureDrawer);
    _MainLayout->addWidget(_MemberName);
    _MainLayout->addLayout(_StateLayout);

    _StateLayout->addWidget(_BusyDrawer);
    _StateLayout->addWidget(_AddTaskButton);

    this->setLayout(_MainLayout);

    this->setStyleSheet("background: #d9d9d9;");
}

