#ifndef CALENDAREVENTFORM_H
#define CALENDAREVENTFORM_H

#include <QDialog>
#include <QLineEdit>
#include <QTextEdit>
#include <QDateEdit>
#include <QTimeEdit>
#include <QFormLayout>
#include <QHBoxLayout>
#include <QPushButton>
#include <QComboBox>
#include <QTableView>
#include <QHeaderView>
#include <QStandardItemModel>
#include <QListWidget>
#include <QByteArray>
#include <QScrollArea>
#include "Calendar/CalendarEvent.h"

class CalendarEventForm : public QDialog
{
    Q_OBJECT
public:
    CalendarEventForm(Event *event, QMap<int, QString> &project, QWidget *callBackEvent);

signals:

public slots:
    void OnSave();
    void OnRemove();

	void OnLoadProjectUserDone(int id, QByteArray data);
	void OnLoadProjectUserFail(int id, QByteArray data);

	void OnLoadEventDone(int id, QByteArray data);
	void OnLoadEventFail(int id, QByteArray data);

	void OnSaveAssociatedDone(int id, QByteArray data);
	void OnSaveAssociatedFail(int id, QByteArray data);

	void OnSaveEventDone(int id, QByteArray data);
	void OnSaveEventFail(int id, QByteArray data);
	
	void OnRemoveEventDone(int id, QByteArray data);
	void OnRemoveEventFail(int id, QByteArray data);

	void OnListUserSelected(int id);
	void OnProjectSelected();

private:
	void EndLoad(bool checkAPILoad = true);

	bool _EventLoaded;

    Event *_CurrentEvent;
	QWidget *_CallBackWidget;
	QList<QPair<QString, QString> > _HexaList;

	QMap<int, int> _PendingCallProject;

	QMap<int, QList<QPair<int, QString> > > _UserProjectsList;
	QMap<int, QList<int> > _AssociatedUserForProject;


    QFormLayout *_MainLayout;

    QLineEdit *_TitleEdit;

    QHBoxLayout *_DateStartLayout;
    QDateEdit *_DateStart;
    QTimeEdit *_TimeStart;

    QHBoxLayout *_DateEndLayout;
    QDateEdit *_DateEnd;
    QTimeEdit *_TimeEnd;

    QTextEdit *_DescriptionEdit;

	QComboBox *_SelectionProject;

	QScrollArea *_Area;
	QVBoxLayout *_UserAssociated;

    QHBoxLayout *_Buttons;
    QPushButton *_Save;
    QPushButton *_Remove;
};

#endif // CALENDAREVENTFORM_H
