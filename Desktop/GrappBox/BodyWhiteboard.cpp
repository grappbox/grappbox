#include <QDebug>
#include <QMenu>
#include <QAction>
#include "BodyWhiteboard.h"

BodyWhiteboard::BodyWhiteboard(QWidget *parent) : QWidget(parent)
{
    _MapId[0] = LINE;
    _MapId[1] = RECT;
    _MapId[2] = CIRCLE;
    _MapId[3] = LOZENGE;
    _MapId[4] = NONE;
    _MapId[5] = HAND_WRITE;
    _MapId[6] = NONE;
    _MapId[7] = ERASER;

    _MainLayout = new QStackedLayout();
    _View = new WhiteboardGraphicsView();
    _Whiteboard = new WhiteboardCanvas();
    _MainLayoutWhiteboard = new QVBoxLayout();
    _MenuLayout = new QHBoxLayout();
    _Table = new QTableView();
    _Table->addAction(new QAction("Blue", _Table));
    _Table->addAction(new QAction("Red", _Table));
    _Table->addAction(new QAction("Green", _Table));
    _Table->addAction(new QAction("White", _Table));
    _ColorPenChoice = new QComboBox();
    _ColorPenChoice->setView(_Table);
    _MenuLayout->addWidget(_ColorPenChoice);
    _MainLayoutWhiteboard->addLayout(_MenuLayout);
    _MainLayoutWhiteboard->addWidget(_View);
    QWidget *whiteboardFrame = new QWidget;
    whiteboardFrame->setLayout(_MainLayoutWhiteboard);
    _MainLayout->addWidget(whiteboardFrame);
    _View->setScene(_Whiteboard);

    setLayout(_MainLayout);
    connect(_View, SIGNAL(OnMenuAction(int)), this, SLOT(OnActionWhiteboard(int)));
}

void BodyWhiteboard::Show(int ID, MainWindow *mainApp)
{
    _WhiteboardId = ID;
    _MainApplication = mainApp;
    show();
}

void BodyWhiteboard::Hide()
{
    hide();
}

void BodyWhiteboard::OnActionWhiteboard(int id)
{
    _Whiteboard->SetGraphicsType(_MapId[id]);
}
