#ifndef WHITEBOARDBUTTONCHOICE_H
#define WHITEBOARDBUTTONCHOICE_H

#include <QtWidgets/QWidget>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QLabel>
#include <QtWidgets/QPushButton>

class BodyDashboard;

class WhiteboardButtonChoice : public QWidget
{
private:
    Q_OBJECT
public:
    WhiteboardButtonChoice(int whiteboardId, BodyDashboard *mainApp, QWidget *parent = NULL);

signals:
    void OnEdit(int);

public slots:
    void Edit();

private:
    QVBoxLayout     *_MainLayout;
    QPushButton     *_EditButton;
    QLabel          *_NameWhiteboard;
    QLabel          *_EditTime;
    QLabel          *_EditDate;

    int             _WhiteboardID;
};

#include "BodyDashboard.h"

#endif // WHITEBOARDBUTTONCHOICE_H
