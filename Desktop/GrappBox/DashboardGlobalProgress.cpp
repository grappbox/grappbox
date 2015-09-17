#include "DashboardGlobalProgress.h"

DashboardGlobalProgress::DashboardGlobalProgress(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    _ProjectInfoLayout = new QHBoxLayout();
    _TextProjectInfoLayout = new QVBoxLayout();
    _ProgressInfoLayout = new QHBoxLayout();

    _ProjectPicture = new QLabel("Picture");
    _ProjectTitle = new QLabel("My project");
    _ProjectCompany = new QLabel("Grappbox");
    _ProjectTel = new QLabel("0123456789");
    _ProjectMail = new QLabel("mon_mail@mon_projet.com");

    _NumberOfTask = new QLabel("4/25");
    _NumberOfMsg = new QLabel("4 messages");
    _NumberOfProblem = new QLabel("7 problems");

    _MainLayout->setSpacing(0);
    _ProjectInfoLayout->setSpacing(0);
    _TextProjectInfoLayout->setSpacing(0);
    _ProgressInfoLayout->setSpacing(0);

    _MainLayout->addLayout(_ProjectInfoLayout);
    _MainLayout->addLayout(_ProgressInfoLayout);
    _ProjectInfoLayout->addWidget(_ProjectPicture);
    _ProjectInfoLayout->addLayout(_TextProjectInfoLayout);
    _TextProjectInfoLayout->addWidget(_ProjectTitle);
    _TextProjectInfoLayout->addWidget(_ProjectCompany);
    _TextProjectInfoLayout->addWidget(_ProjectTel);
    _TextProjectInfoLayout->addWidget(_ProjectMail);
    _ProgressInfoLayout->addWidget(_NumberOfTask);
    _ProgressInfoLayout->addWidget(_NumberOfMsg);
    _ProgressInfoLayout->addWidget(_NumberOfProblem);

    setLayout(_MainLayout);
}

