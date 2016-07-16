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
#include <QCheckBox>
#include <QComboBox>
#include <QTableView>
#include <QHeaderView>
#include <QStandardItemModel>
#include <QListWidget>
#include <QByteArray>
#include <QScrollArea>
#include "Calendar/CalendarEvent.h"
#include "Body/Settings/ImageUploadWidget.h"

class CalendarEventForm : public QDialog
{
    Q_OBJECT
public:
    CalendarEventForm(Event *event, QMap<int, QString> &project, QWidget *callBackEvent);

signals:
	void Remove(Event*);
	void Create(QDateTime, QDateTime);

public slots:
    void OnSave();
    void OnRemove();

	void OnEventTypeLoadDone(int id, QByteArray data);
	void OnEventTypeLoadFail(int id, QByteArray data);

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
	bool _TypeLoaded;

    Event *_CurrentEvent;
	QWidget *_CallBackWidget;
	QList<QPair<QString, QString> > _HexaList;

	QMap<int, int> _PendingCallProject;

	QMap<int, QList<QPair<int, QString> > > _UserProjectsList;
	QMap<int, QList<int> > _AssociatedUserForProject;
	QMap<int, QString> _Type;

	QList<int> _IdsAtStart;

    QFormLayout *_MainLayout;

    QLineEdit *_TitleEdit;

    QHBoxLayout *_DateStartLayout;
    QDateEdit *_DateStart;
    QTimeEdit *_TimeStart;

    QHBoxLayout *_DateEndLayout;
    QDateEdit *_DateEnd;
    QTimeEdit *_TimeEnd;

    QTextEdit *_DescriptionEdit;

	QComboBox *_SelectionType;
	QCheckBox *_UseTypeIcon;
	ImageUploadWidget *_UploadWidget;

	QComboBox *_SelectionProject;

	QScrollArea *_AreaAssociated;
	QVBoxLayout *_UserAssociated;
	QScrollArea *_AreaNotAssociated;
	QVBoxLayout *_UserNotAssociated;

    QHBoxLayout *_Buttons;
    QPushButton *_Save;
    QPushButton *_Remove;
};

#endif // CALENDAREVENTFORM_H
