#include "BugCheckableLabel.h"

BugCheckableLabel::BugCheckableLabel(const int id, const QString &name, const bool checked, QWidget *parent) : QWidget(parent)
{
    _mainLayout = new QHBoxLayout();
    _checked = new QCheckBox(name);
    _id = id;

    QObject::connect(_checked, SIGNAL(clicked(bool)), this, SLOT(TriggerCheckChange(bool)));
    _checked->setChecked(checked);
    _mainLayout->addWidget(_checked);
    this->setLayout(_mainLayout);
}

void BugCheckableLabel::TriggerCheckChange(bool checked)
{
    emit OnCheckChanged(checked, _id, _checked->text());
}

const int BugCheckableLabel::GetId() const
{
    return _id;
}

const QString &BugCheckableLabel::GetName() const
{
    return _checked->text();
}

const bool BugCheckableLabel::IsChecked() const
{
    return _checked->isChecked();
}
