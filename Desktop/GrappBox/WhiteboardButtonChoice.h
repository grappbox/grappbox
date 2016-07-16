#ifndef WHITEBOARDBUTTONCHOICE_H
#define WHITEBOARDBUTTONCHOICE_H

#include <QtWidgets/QWidget>
#include <QtWidgets/QVBoxLayout>
#include <QtWidgets/QHBoxLayout>
#include <QtWidgets/QLabel>
#include <QtWidgets/QPushButton>
#include <QDateTime>
#include "PushButtonImage.h"

class BodyDashboard;

struct Whiteboard
{
	int id;
	int projectId;
	QDateTime lastModification;
	QDateTime creation;
	QString name;
};

class WhiteboardButtonChoice : public QWidget
{
private:
    Q_OBJECT
public:
    WhiteboardButtonChoice(Whiteboard w, QWidget *parent = nullptr);

signals:
    void OnEdit(int);
	void OnDelete(int);

public slots:
    void Edit();
	void Delete();

private:
    QVBoxLayout     *_MainLayout;
    QPushButton     *_EditButton;
    QLabel          *_NameWhiteboard;
    QLabel          *_CreateDate;
    QLabel          *_EditDate;

    Whiteboard      _WhiteboardID;
};

#include "BodyDashboard.h"

#endif // WHITEBOARDBUTTONCHOICE_H
