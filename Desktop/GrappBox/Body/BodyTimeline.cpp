#include <QTabBar>
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

    _ClientTimeline = new CanvasTimeline(1);
    _TeamTimeline = new CanvasTimeline(2);

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

    QObject::connect(_ButtonToClient, SIGNAL(clicked(bool)), this, SLOT(OnChange()));
    QObject::connect(_ButtonToTeam, SIGNAL(clicked(bool)), this, SLOT(OnChange()));
}

void BodyTimeline::Show(int ID, MainWindow *mainApp)
{
    emit OnLoadingDone(ID);
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


