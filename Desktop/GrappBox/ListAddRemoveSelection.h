#pragma once

#include <QWidget>
#include <QLabel>
#include <QPushButton>
#include <QHBoxLayout>

class ListAddRemoveSelection : public QWidget
{
	Q_OBJECT
public:
	ListAddRemoveSelection(int id, bool isAdd, QString name, QString customButtonText = QString::null);

signals:
	void Selected(int);

public slots:
	void OnSelected();

private:
	int _Id;

	QHBoxLayout *_MainLayout;

	QLabel		*_Label;
	QPushButton	*_Button;
};

