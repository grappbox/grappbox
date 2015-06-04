#ifndef WHITEBOARD_H
#define WHITEBOARD_H

#include <QtWidgets/QMainWindow>
#include "ui_whiteboard.h"

class Whiteboard : public QMainWindow
{
	Q_OBJECT

public:
	Whiteboard(QWidget *parent = 0);
	~Whiteboard();

private:
	Ui::WhiteboardClass ui;
};

#endif // WHITEBOARD_H
