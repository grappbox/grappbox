#include "mainwindow.h"
#include "ui_mainwindow.h"

MainWindow::MainWindow(QWidget *parent) :
    QMainWindow(parent),
    ui(new Ui::MainWindow)
{
    ui->setupUi(this);
    whiteboard = new whiteboardcanva();
    ui->graphicsView->setScene(whiteboard);
    ui->graphicsView->setSceneRect(ui->graphicsView->width() / -2, ui->graphicsView->height() / -2, ui->graphicsView->width(), ui->graphicsView->height());
    ui->verticalLayout->setStretch(1, 1);
}

MainWindow::~MainWindow()
{
    delete ui;
}

void MainWindow::on_actionQuit_triggered()
{
    this->close();
}

void MainWindow::on_Circle_clicked()
{
    whiteboard->SetGraphicsType(GraphicsType::CIRCLE);
}

void MainWindow::on_Rect_clicked()
{
    whiteboard->SetGraphicsType(GraphicsType::RECT);
}

void MainWindow::on_Line_clicked()
{
    whiteboard->SetGraphicsType(GraphicsType::LINE);
}

void MainWindow::on_eraseRadio_clicked()
{

}
