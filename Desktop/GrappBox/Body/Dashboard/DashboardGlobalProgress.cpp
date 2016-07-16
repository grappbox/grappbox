#include "DashboardGlobalProgress.h"
#include "SFontLoader.h"
#include <sstream>

DashboardGlobalProgress::DashboardGlobalProgress(DashboardInformation::GlobalProgressInfo *info, QWidget *parent) : QWidget(parent)
{
    setFixedSize(320, 160);

    _MainLayout = new QVBoxLayout();
    _ProjectInfoLayout = new QHBoxLayout();
    _TextProjectInfoLayout = new QVBoxLayout();
    _ProgressInfoLayout = new QHBoxLayout();

    QFont font = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    font.setPointSize(10);
    font.setBold(true);
    QFont fontTitle = SFontLoader::GetFont(SFontLoader::OPEN_SANS_BOLD);
    fontTitle.setPointSize(15);
    fontTitle.setBold(true);

    _ProjectPicture = new QLabel();
    info->ProjectPicture = new QPixmap(info->ProjectPicture->scaled(64, 64));
    _ProjectPicture->setPixmap(*info->ProjectPicture);
    _ProjectPicture->setFixedSize(120, 120);
    _ProjectPicture->setAlignment(Qt::AlignHCenter | Qt::AlignVCenter);
    _ProjectTitle = new QLabel(info->ProjectTitle);
    _ProjectTitle->setFont(fontTitle);
    _ProjectTitle->setStyleSheet("QLabel { color: #ffffff }");
    _ProjectCompany = new QLabel(info->ProjectCompany);
    _ProjectCompany->setFont(font);
    _ProjectCompany->setStyleSheet("QLabel { color: #969797 }");
    _ProjectTel = new QLabel(info->ProjectTel);
    _ProjectTel->setFont(font);
    _ProjectTel->setStyleSheet("QLabel { color: #969797 }");
    _ProjectMail = new QLabel(info->ProjectMail);
    _ProjectMail->setFont(font);
    _ProjectMail->setStyleSheet("QLabel { color: #969797 }");

    std::stringstream taskStr("");
    std::stringstream msgStr("");
    std::stringstream problemStr("");
    taskStr << info->NumberTask << '/' << info->MaxNumberTask;
    msgStr << info->NumberMsg << " messages";
    problemStr << info->NumberProblem << " problems";

    _NumberOfTask = new QLabel(QString(taskStr.str().c_str()));
    _NumberOfTask->setFont(font);
    _NumberOfTask->setStyleSheet("QLabel { color: #ffffff }");
    _NumberOfMsg = new QLabel(QString(msgStr.str().c_str()));
    _NumberOfMsg->setFont(font);
    _NumberOfMsg->setStyleSheet("QLabel { color: #ffffff }");
    _NumberOfProblem = new QLabel(QString(problemStr.str().c_str()));
    _NumberOfProblem->setFont(font);
    _NumberOfProblem->setStyleSheet("QLabel { color: #ffffff;border-bottom-right-radius: 10px;  }");

    _TaskIcon = new QLabel();
    _MsgIcon = new QLabel();
    _ProblemIcon = new QLabel();
    _TaskIcon->setPixmap(QPixmap(":/Temporary/GlobalProgressType/Ressources/Temporary/GlobalProgressIcon/Task.png"));
    _MsgIcon->setPixmap(QPixmap(":/Temporary/GlobalProgressType/Ressources/Temporary/GlobalProgressIcon/Message.png"));
    _ProblemIcon->setPixmap(QPixmap(":/Temporary/GlobalProgressType/Ressources/Temporary/GlobalProgressIcon/Bug.png"));

    _TaskIcon->setFixedSize(40, 40);
    _MsgIcon->setFixedSize(40, 40);
    _ProblemIcon->setFixedSize(40, 40);

    _TaskIcon->setStyleSheet("QLabel { border-bottom-left-radius: 10px; }");

    _MainLayout->setSpacing(0);
    _MainLayout->setMargin(0);
    _ProjectInfoLayout->setSpacing(0);
    _TextProjectInfoLayout->setSpacing(0);
    _ProgressInfoLayout->setSpacing(0);

    _ProgressInfoLayout->addWidget(_TaskIcon);
    _ProgressInfoLayout->addWidget(_NumberOfTask);
    _ProgressInfoLayout->addWidget(_MsgIcon);
    _ProgressInfoLayout->addWidget(_NumberOfMsg);
    _ProgressInfoLayout->addWidget(_ProblemIcon);
    _ProgressInfoLayout->addWidget(_NumberOfProblem);
    QWidget *ProgressInfoWidget = new QWidget();
    ProgressInfoWidget->setLayout(_ProgressInfoLayout);
    ProgressInfoWidget->setStyleSheet("background: #af2d2e; border-bottom-left-radius: 10px; border-bottom-right-radius: 10px");
    _MainLayout->addLayout(_ProjectInfoLayout, 120);
    _MainLayout->addWidget(ProgressInfoWidget, 40);
    _ProjectInfoLayout->addWidget(_ProjectPicture);
    _ProjectInfoLayout->addLayout(_TextProjectInfoLayout);
    _TextProjectInfoLayout->addWidget(_ProjectTitle);
    _TextProjectInfoLayout->addWidget(_ProjectCompany);
    _TextProjectInfoLayout->addWidget(_ProjectTel);
    _TextProjectInfoLayout->addWidget(_ProjectMail);

    setLayout(_MainLayout);
    this->setObjectName("DashboardGlobalProgress");
    this->setStyleSheet("DashboardGlobalProgress {background: #2d2f31;"
                        "border-radius: 10px;"
                        "border: none;}");
}

void DashboardGlobalProgress::paintEvent(QPaintEvent *)
 {
     QStyleOption opt;
     opt.init(this);
     QPainter p(this);
     style()->drawPrimitive(QStyle::PE_Widget, &opt, &p, this);
 }
