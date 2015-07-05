#include "mainwindow.h"
#include "ui_mainwindow.h"

MainWindow::MainWindow(QWidget *parent) :
    QMainWindow(parent),
    m_ui(new Ui::MainWindow)
{
    m_ui->setupUi(this);
    m_whiteboard = new Whiteboard();
    m_ui->mainLayout->addWidget(m_whiteboard);

    QObject::connect(m_ui->actionSquare, SIGNAL(triggered()), m_whiteboard, SLOT(setSquareTool()));
    QObject::connect(m_ui->actionCircle, SIGNAL(triggered()), m_whiteboard, SLOT(setCircleTool()));
    QObject::connect(m_ui->actionLine, SIGNAL(triggered()), m_whiteboard, SLOT(setLineTool()));

}

MainWindow::~MainWindow()
{
    delete m_ui;
}
