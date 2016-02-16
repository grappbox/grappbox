#pragma once
#include <QWidget>
#include <QVBoxLayout>
#include <QLabel>
#include <QLineEdit>
#include <QPushButton>
#include <QDialog>

class CreateWhiteboardDialog : public QDialog
{
	Q_OBJECT
public:
	CreateWhiteboardDialog(QWidget *parent);
	~CreateWhiteboardDialog();

signals:
	void NewWhiteboard();

public slots:
	void OnCreateWhiteboardDone(int, QByteArray data);
	void OnCreateWhiteboardFail(int, QByteArray data);

	void OnCreate();

private:
	QLineEdit *_WhiteboardName;
};

