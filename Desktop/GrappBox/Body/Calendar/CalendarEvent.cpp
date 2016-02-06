#include <QDebug>
#include <QApplication>
#include <QEvent>
#include <QMouseEvent>
#include <QDesktopWidget>
#include "SDataManager.h"
#include "utils.h"
#include "CalendarEvent.h"

CalendarEvent::CalendarEvent(Event *event, QWidget *parent) : QWidget(parent)
{
	_Label = new QLabel(QString("[") + event->Start.time().toString("hh:mm") + "] " + event->Title);

	setStyleSheet("background: " + event->Color.name() + ";");

	_MainLayout = new QHBoxLayout();
	_MainLayout->addWidget(_Label);
	_MainLayout->setSpacing(0);
	_MainLayout->setContentsMargins(2, 0, 0, 2);

	setAttribute(Qt::WA_Hover, true);
	setMouseTracking(true);

	_Event = event;

	_Popup = new QWidget();
	_Popup->setWindowFlags(Qt::Popup);
	_MainLayoutPopup = new QGridLayout();
	_Title = new QLabel(event->Title);
	_Description = new QLabel(event->Description);
	_Date = new QLabel("From " + event->Start.toString("yyyy-MM-dd hh:mm") + " to " + event->End.toString("yyyy-MM-dd hh:mm"));
	_ProjectLinked = new QLabel("Project : " + event->Project);
	_Edit = new QPushButton("Edit");
	connect(_Edit, SIGNAL(clicked(bool)), this, SLOT(OnEdit()));
	_Delete = new QPushButton("Delete");
	connect(_Delete, SIGNAL(clicked(bool)), this, SLOT(OnDelete()));
	_Quit = new PushButtonImage();
	_Quit->SetImage(QPixmap(":/icon/Ressources/Icon/Return.png"));
	_Quit->setFixedSize(24, 24);
	connect(_Quit, SIGNAL(clicked(bool)), this, SLOT(OnQuit()));
	_Quit->SetColors(QColor(30, 30, 30), QColor(202, 67, 53), QColor(192, 57, 43));
	if (event->CreatorId != USER_ID)
	{
		_Edit->setDisabled(true);
		_Delete->setDisabled(true);
	}
	_MainLayoutPopup->addWidget(_Title, 0, 0, 1, 1);
	_MainLayoutPopup->addWidget(_Quit, 0, 1, 1, 1);
	_MainLayoutPopup->addWidget(_Date, 1, 0, 1, 2);
	_MainLayoutPopup->addWidget(_Description, 2, 0, 2, 2);
	_MainLayoutPopup->addWidget(_ProjectLinked, 4, 0, 1, 2);
	_MainLayoutPopup->addWidget(_Edit, 5, 0, 1, 1);
	_MainLayoutPopup->addWidget(_Delete, 5, 1, 1, 1);
	_Popup->setLayout(_MainLayoutPopup);

	_Popup->setFixedSize(400, 200);

	setLayout(_MainLayout);
}

void CalendarEvent::enterEvent(QEvent * event)
{
	QWidget::enterEvent(event);
	QApplication::setOverrideCursor(Qt::PointingHandCursor);
}

void CalendarEvent::leaveEvent(QEvent * event)
{
	QWidget::leaveEvent(event);
	QApplication::restoreOverrideCursor();
}

void CalendarEvent::mousePressEvent(QMouseEvent * event)
{
	if (event->button() == Qt::MouseButton::LeftButton)
	{
		QPointF pos = event->screenPos();
		pos -= QPointF(_Popup->size().width() / 2, _Popup->size().height() / 2);
		QRect rec = QApplication::desktop()->screenGeometry();
		if (pos.x() < 0)
			pos.setX(20);
		if (pos.x() + _Popup->size().width() > rec.size().width())
			pos.setX(pos.x() - (rec.size().width() - (pos.x() + _Popup->size().width())) + 20);
		if (pos.y() < 0)
			pos.setY(20);
		if (pos.y() + _Popup->size().height() > rec.size().height())
			pos.setY(pos.y() - (rec.size().height() - (pos.y() + _Popup->size().height())) + 20);
		_Popup->move(pos.x(), pos.y());
		_Popup->show();
	}
}

void CalendarEvent::OnEdit()
{
	_Popup->hide();
	emit NeedEdit(_Event);
}

void CalendarEvent::OnDelete()
{
	_Popup->hide();
	emit NeedDelete(_Event);
}

void CalendarEvent::OnQuit()
{
	_Popup->hide();
}

const Event *CalendarEvent::GetEvent() const
{
	return _Event;
}
