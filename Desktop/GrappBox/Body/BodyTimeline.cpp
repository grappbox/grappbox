#include <QTabBar>
#include <QMessageBox>
#include <QJsonDocument>
#include <QJsonObject>
#include <QJsonValueRef>
#include <QJsonArray>
#include <QDebug>
#include "BodyTimeline.h"

BodyTimeline::BodyTimeline(QWidget *parent) : QWidget(parent)
{
    _MainLayout = new QVBoxLayout();
    _MainLayout->setSpacing(0);
    _MainLayout->setContentsMargins(0, 0, 0, 0);
    _MainLayoutTimeline = new QStackedLayout();
    _MainLayoutTimeline->setSpacing(0);
    _MainLayoutTimeline->setContentsMargins(0, 0, 0, 0);
    _MainLayoutButton = new QHBoxLayout();
    _MainLayoutButton->setSpacing(0);
    _MainLayoutButton->setContentsMargins(0, 0, 0, 0);

    _ButtonToClient = new QPushButton("Client");
    _ButtonToTeam = new QPushButton("Team");

    QString styleSheetButton = "QPushButton {"
                               "height: 32px;"
                               "border-style:none;"
                               "border-bottom-style:solid;"
                               "border-width: 4px;"
                               "border-color: #f0f3f7;"
                               "background: #FFFFFF;}"
                               "QPushButton:disabled {"
                               "border-color: #af2d2e;}"
                               ;

    _ButtonToClient->setStyleSheet(styleSheetButton);
    _ButtonToTeam->setStyleSheet(styleSheetButton);
    _MainLayoutButton->addWidget(_ButtonToTeam);
    _MainLayoutButton->addWidget(_ButtonToClient);

    _ClientSA = new QScrollArea();
    _TeamSA = new QScrollArea();

    _ClientTimeline = new CanvasTimeline();
    _TeamTimeline = new CanvasTimeline();

    _ClientSA->setWidget(_ClientTimeline);
    _ClientSA->setWidgetResizable(true);
    _ClientSA->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    _ClientSA->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);

    _TeamSA->setWidget(_TeamTimeline);
    _TeamSA->setWidgetResizable(true);
    _TeamSA->setHorizontalScrollBarPolicy(Qt::ScrollBarAlwaysOff);
    _TeamSA->setVerticalScrollBarPolicy(Qt::ScrollBarAlwaysOn);

    _IDButtonTeam = _MainLayoutTimeline->addWidget(_TeamSA);
    _IDButtonClient = _MainLayoutTimeline->addWidget(_ClientSA);

    _MainLayout->addLayout(_MainLayoutButton);
    _MainLayout->addLayout(_MainLayoutTimeline);

    _ButtonToTeam->setDisabled(true);

    setLayout(_MainLayout);
    setSizePolicy(QSizePolicy::Expanding, QSizePolicy::Expanding);

    QObject::connect(_ClientTimeline, SIGNAL(OnFinishedLoading(int)), this, SLOT(OnTimelineSuccessLoad(int)));
    QObject::connect(_TeamTimeline, SIGNAL(OnFinishedLoading(int)), this, SLOT(OnTimelineSuccessLoad(int)));
    QObject::connect(_ButtonToClient, SIGNAL(clicked(bool)), this, SLOT(OnChange()));
    QObject::connect(_ButtonToTeam, SIGNAL(clicked(bool)), this, SLOT(OnChange()));
}

void BodyTimeline::OnTimelineGet(int ID, QByteArray data)
{
    QJsonDocument doc = QJsonDocument::fromJson(data);
    QJsonObject objMain = doc.object();
	SHOW_JSON(data);
    for (QJsonValueRef ref : objMain["timelines"].toArray())
    {
        QJsonObject obj = ref.toObject();
        qDebug() << obj;
        if (obj["typeId"].toInt() == 1)
            _ClientTimeline->LoadData(obj["id"].toInt());
        else
            _TeamTimeline->LoadData(obj["id"].toInt());
    }
}

void BodyTimeline::OnTimelineFailGet(int ID, QByteArray array)
{
    QMessageBox::critical(this, "Timeline", "Impossible to retrieve timeline from projects. Please contact an administrator.");
}

void BodyTimeline::OnTimelineSuccessLoad(int ID)
{
    _TimelineLoading.removeAll(ID);
    if (_TimelineLoading.size() == 0)
        emit OnLoadingDone(_IdWidget);
}

void BodyTimeline::Show(int ID, MainWindow *mainApp)
{
    _IdWidget = ID;
    _MainApp = mainApp;
    QVector<QString> data;
    data.push_back(API::SDataManager::GetDataManager()->GetToken());
    data.push_back(QVariant(API::SDataManager::GetDataManager()->GetCurrentProject()).toString());
    API::SDataManager::GetCurrentDataConnector()->Get(API::DP_TIMELINE, API::GR_LIST_TIMELINE, data, this, "OnTimelineGet", "OnTimelineFailGet");

}

void BodyTimeline::Hide()
{

}

void BodyTimeline::OnChange()
{
    if (_MainLayoutTimeline->currentIndex() == _IDButtonClient)
    {
        _ButtonToClient->setDisabled(false);
        _ButtonToTeam->setDisabled(true);
        _MainLayoutTimeline->setCurrentIndex(_IDButtonTeam);
    }
    else
    {
        _ButtonToClient->setDisabled(true);
        _ButtonToTeam->setDisabled(false);
        _MainLayoutTimeline->setCurrentIndex(_IDButtonClient);
    }
}


