#include "ListAddRemoveSelection.h"

ListAddRemoveSelection::ListAddRemoveSelection(int id, bool isAdd, QString name, QString customButtonText)
{
	_MainLayout = new QHBoxLayout();

	_Label = new QLabel(name);
	_Button = new QPushButton(customButtonText.isNull() ? (isAdd ? "+" : "-") : customButtonText);
	_Button->setFixedWidth(60);

	_MainLayout->addWidget(_Label);
	_MainLayout->addWidget(_Button);

	setLayout(_MainLayout);

	setFixedHeight(50);

	_Id = id;

	QObject::connect(_Button, SIGNAL(clicked(bool)), this, SLOT(OnSelected()));
}

void ListAddRemoveSelection::OnSelected()
{
	emit Selected(_Id);
}
